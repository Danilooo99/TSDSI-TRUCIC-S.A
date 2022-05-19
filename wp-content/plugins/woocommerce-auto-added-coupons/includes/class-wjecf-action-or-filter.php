<?php

/**
 * Class that prevents an action or filter to be recursively called
 */
class WJECF_Action_Or_Filter {


	private $tag;
	private $function_to_add;
	private $priority;
	private $accepted_args;
	private $limit_calls;

	/**
	 *
	 * @param string $tag name of the action or filter
	 * @param callable $function_to_add
	 * @param int $priority
	 * @param int $accepted_args
	 * @param int $limit_calls When > 0 the calls will be limited to this amount (prevents recursive calls)
	 */
	private function __construct( $tag, $function_to_add, $priority, $accepted_args, $limit_calls = 0 ) {
		$this->tag             = $tag;
		$this->function_to_add = $function_to_add;
		$this->priority        = $priority;
		$this->accepted_args   = $accepted_args;

		$this->limit_calls = $limit_calls;
	}

	private $inhibit = false;
	private $counter = 0;

	//Must be public for WC
	public function execute() {
		if ( $this->inhibit ) {
			return;
		}

		$this->counter++;

		$this->inhibit = true;
		$func_args     = func_get_args(); // $func_args variable required for PHP5.2
		$retval        = call_user_func_array( $this->function_to_add, $func_args );
		$this->inhibit = false;

		if ( $this->limit_calls > 0 && $this->counter >= $this->limit_calls ) {
			remove_action( $this->tag, array( $this, 'execute' ), $this->priority ); //unhook the action
		}

		return $retval;
	}

	/**
	 * Same as WordPress add_action(), but prevents the callback to be recursively called
	 *
	 * @param string $tag
	 * @param callable $function_to_add
	 * @param int $priority
	 * @param int $accepted_args
	 */
	public static function action( $tag, $function_to_add, $priority = 10, $accepted_args = 1, $limit_calls = 0 ) {
		$me = new WJECF_Action_Or_Filter( $tag, $function_to_add, $priority, $accepted_args, $limit_calls );
		add_action( $tag, array( $me, 'execute' ), $priority, $accepted_args );
	}
}
