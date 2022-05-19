<?php
if (!class_exists('WPFA_Change_Texts')) {

	class WPFA_Change_Texts {

		static private $instance = false;
		var $current_page_edits = false;

		private function __construct() {
			
		}

		function init() {
			if (VG_Admin_To_Frontend_Obj()->get_settings('disable_replacements_on_post_requests')) {
				return;
			}


			// FIX. Rank math settings don't save when this feature is running. We can't add 
			// this code in the rank-math.php file because this runs very early
			$all_post_data = serialize($_POST);
			if (!empty($_POST) && strpos($all_post_data, 'rank-math') !== false) {
				return;
			}
			if (!apply_filters('wp_frontend_admin/can_load_change_texts_feature', true)) {
				return;
			}

			if (is_admin()) {
				add_action('wp_frontend_admin/quick_settings/after_save', array($this, 'save_meta_box'), 10, 2);
				if (!is_network_admin()) {
					add_action('admin_init', array($this, 'start_buffer_for_replacement'), 1);
					add_action('shutdown', array($this, 'buffer_end'));
				}
				if (dapof_fs()->can_use_premium_code__premium_only()) {
					add_action('vg_plugin_sdk/settings/' . VG_Admin_To_Frontend::$textname . '/options', array($this, 'add_settings_tab__premium_only'));
				}
			} else {
				add_action('wp_frontend_admin/quick_settings/after_fields', array($this, 'render_meta_box'));
			}
		}

		function get_disallowed_keywords() {
			$html_tags = array(
				"a",
				"abbr",
				"acronym",
				"address",
				"applet",
				"area",
				"article",
				"aside",
				"audio",
				"b",
				"base",
				"basefont",
				"bdi",
				"bdo",
				"bgsound",
				"big",
				"blink",
				"blockquote",
				"body",
				"br",
				"button",
				"canvas",
				"caption",
				"center",
				"cite",
				"code",
				"col",
				"colgroup",
				"command",
				"content",
				"data",
				"datalist",
				"dd",
				"del",
				"details",
				"dfn",
				"dialog",
				"dir",
				"div",
				"dl",
				"dt",
				"element",
				"em",
				"embed",
				"fieldset",
				"figcaption",
				"figure",
				"font",
				"footer",
				"form",
				"frame",
				"frameset",
				"h1",
				"h2",
				"h3",
				"h4",
				"h5",
				"h6",
				"head",
				"header",
				"hgroup",
				"hr",
				"html",
				"i",
				"iframe",
				"image",
				"img",
				"input",
				"ins",
				"isindex",
				"kbd",
				"keygen",
				"label",
				"legend",
				"li",
				"link",
				"listing",
				"main",
				"map",
				"mark",
				"marquee",
				"math",
				"menu",
				"menuitem",
				"meta",
				"meter",
				"multicol",
				"nav",
				"nextid",
				"nobr",
				"noembed",
				"noframes",
				"noscript",
				"object",
				"ol",
				"optgroup",
				"option",
				"output",
				"p",
				"param",
				"picture",
				"plaintext",
				"pre",
				"progress",
				"q",
				"rb",
				"rbc",
				"rp",
				"rt",
				"rtc",
				"ruby",
				"s",
				"samp",
				"script",
				"section",
				"select",
				"shadow",
				"slot",
				"small",
				"source",
				"spacer",
				"span",
				"strike",
				"strong",
				"style",
				"sub",
				"summary",
				"sup",
				"svg",
				"table",
				"tbody",
				"td",
				"template",
				"textarea",
				"tfoot",
				"th",
				"thead",
				"time",
				"title",
				"tr",
				"track",
				"tt",
				"u",
				"ul",
				"var",
				"video",
				"wbr",
				"xmp"
			);
			return apply_filters('wp_frontend_admin/global_replacement/disallowed_search_strings', $html_tags);
		}

		function add_settings_tab__premium_only($sections) {
			$sections['global-replacement'] = array(
				'title' => __('Global replacements', VG_Admin_To_Frontend::$textname),
				'fields' => array(
					array(
						'id' => 'global_replacements',
						'type' => 'multi_text_repeater',
						'title' => __('Global replacements', VG_Admin_To_Frontend::$textname),
						'desc' => __('You can use this to replace texts globally, URLs, logo images (URLs), or remove html elements. You can use regex, just make sure the search string starts and ends with /', VG_Admin_To_Frontend::$textname),
						'first_placeholder' => __('Replace this', VG_Admin_To_Frontend::$textname),
						'second_placeholder' => __('with this', VG_Admin_To_Frontend::$textname),
					)
				)
			);
			$sections['solutions']['fields'][] = array(
				'id' => 'disable_replacements_on_post_requests',
				'type' => 'switch',
				'title' => __('Disable the replacements on POST requests?', VG_Admin_To_Frontend::$textname),
				'desc' => __('Sometimes, due to rare server configurations, settings pages will not save changes. Activate this option to disable all the text replacements on those requests, which sometimes make the saving work.', VG_Admin_To_Frontend::$textname),
			);
			return $sections;
		}

		function get_text_edits_for_current_page() {
			global $wpdb;
			$out = array();

			$vgfa = VG_Admin_To_Frontend_Obj();
			$url_path = $vgfa->prepare_loose_url($vgfa->get_current_url());

			$all_text_edits = (array) $vgfa->get_current_page_settings($url_path, 'vgfa_text_changes', array());

			if (empty($all_text_edits)) {
				return $out;
			}

			foreach ($all_text_edits as $raw_text_edits) {
				$text_edits = json_decode($raw_text_edits, true);

				if (!is_array($text_edits)) {
					continue;
				}

				foreach ($text_edits as $url => $edits) {
					if (empty($url) || empty($url_path) || strpos($url, $url_path) === false) {
						continue;
					}

					$out = array_merge($out, $edits);
				}
			}

			return $out;
		}

		function start_buffer_for_replacement() {
			// We apply the replacement for all users, including the master user
			// to be able to see the preview when editing		
			$vgfa = VG_Admin_To_Frontend_Obj();
			if ($vgfa->is_master_user() && empty($_GET['vgfa_source'])) {
				return;
			}

			$text_edits = $this->get_text_edits_for_current_page();

			if (dapof_fs()->can_use_premium_code__premium_only()) {
				$global_replacements_raw = VG_Admin_To_Frontend_Obj()->get_settings('global_replacements', array());
				$global_replacements = array_combine(wp_list_pluck($global_replacements_raw, 0), wp_list_pluck($global_replacements_raw, 1));
				$text_edits = array_merge($text_edits, $global_replacements);
			}
			if (empty($text_edits)) {
				return;
			}
			$text_edits = apply_filters('wp_frontend_admin/text_edits_for_current_page', $text_edits);
			$this->current_page_edits = array();
			$disallowed_keywords = $this->get_disallowed_keywords();
			foreach ($text_edits as $search => $replace) {
				// Don't apply replacement if the search is empty, search is < 4 letters, 
				// search and replace are the same, or search and replace are empty
				if (empty($search) || strlen($search) < 4 || (empty($search) && empty($replace)) || $search === $replace || in_array($search, $disallowed_keywords, true)) {
					continue;
				}
				$search = wp_unslash($search);
				$replace = wp_unslash($replace);
				$this->current_page_edits[$search] = $replace;
			}

			if (!empty($this->current_page_edits)) {
				ob_start(array($this, 'replace'));
			}
		}

		function replace($buffer) {
			if (empty($this->current_page_edits)) {
				return $buffer;
			}

			$buffer = apply_filters('wp_frontend_admin/change_texts/buffer_before_replacement', $buffer, $this->current_page_edits);
			foreach ($this->current_page_edits as $search => $replace) {
				if ($search === 'Role') {
					$search = '/\bRole\b/';
				}
				if ($search === 'Roles') {
					$search = '/\bRoles\b/';
				}
				// Make regex replacement if string starts and ends with /
				if (substr($search, 0, 1) === "/" && substr($search, -1) === "/") {
					$buffer = preg_replace($search, $replace, $buffer);
				} else {
					$buffer = str_replace($search, $replace, $buffer);
				}
			}
			$buffer = apply_filters('wp_frontend_admin/change_texts/buffer_after_replacement', $buffer, $this->current_page_edits);

			return $buffer;
		}

		function buffer_end() {
			if (!$this->current_page_edits) {
				return;
			}
			if (!ob_get_contents()) {
				return;
			}
			ob_end_flush();
		}

		/**
		 * Meta box display callback.
		 *
		 * @param WP_Post $post Current post object.
		 */
		function render_meta_box($post) {
			?>
			<div class="field">
				<button class="edit-text-trigger"><span class="dashicons dashicons-edit"></span> <?php _e('Edit texts', VG_Admin_To_Frontend::$textname); ?></button>
				<button class="stop-edit-text-trigger"><?php _e('Stop editing texts', VG_Admin_To_Frontend::$textname); ?></button>
				<a class="revert-all-text-edits-trigger" href="#"><?php _e('Revert changes', VG_Admin_To_Frontend::$textname); ?></a>		
				<input type="hidden" class="text-changes-input" name="vgfa_text_changes" value="<?php echo esc_attr(get_post_meta(get_the_ID(), 'vgfa_text_changes', true)); ?>">
			</div>
			<hr>
			<?php
		}

		function save_meta_box($post_id, $post) {

			if (!isset($_REQUEST['vgfa_text_changes'])) {
				return;
			}
			$changes = wp_unslash(sanitize_text_field($_REQUEST['vgfa_text_changes']));
			if (!empty($changes)) {
				$existing_changes = get_post_meta($post_id, 'vgfa_text_changes', true);
				if (!empty($existing_changes) && is_string($existing_changes)) {
					$existing_changes = json_decode($existing_changes, true);
				}
				if (empty($existing_changes) || !is_array($existing_changes)) {
					$existing_changes = array();
				}
				$vgfa = VG_Admin_To_Frontend_Obj();
				$prepared_changes = json_decode($changes, true);
				foreach ($prepared_changes as $url => $change) {
					$url_path = $vgfa->prepare_loose_url($url);
					if (!isset($prepared_changes[$url_path])) {
						$prepared_changes[$url_path] = array();
					}
					$existing_changes_for_path = isset($existing_changes[$url_path]) ? $existing_changes[$url_path] : array();
					$prepared_changes[$url_path] = array_merge($existing_changes_for_path, $prepared_changes[$url_path], $change);
					if ($url !== $url_path) {
						unset($prepared_changes[$url]);
					}
				}
				// Add the existing text changes, that apply to pages that weren't edited in this session
				foreach ($existing_changes as $url => $change) {
					if (!isset($prepared_changes[$url])) {
						$prepared_changes[$url] = $change;
					}
				}
				foreach ($prepared_changes as $url => $changes) {
					foreach ($changes as $search => $replace) {
						if (empty($search) || strlen($search) < 4) {
							unset($prepared_changes[$url][$search]);
						}
					}
				}
				// JSON_UNESCAPED_UNICODE is needed to save german characters correctly
				$changes = json_encode($prepared_changes, JSON_UNESCAPED_UNICODE);
			}
			update_post_meta($post_id, 'vgfa_text_changes', $changes);
		}

		/**
		 * Creates or returns an instance of this class.
		 */
		static function get_instance() {
			if (null == WPFA_Change_Texts::$instance) {
				WPFA_Change_Texts::$instance = new WPFA_Change_Texts();
				WPFA_Change_Texts::$instance->init();
			}
			return WPFA_Change_Texts::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}

if (!function_exists('WPFA_Change_Texts_Obj')) {

	function WPFA_Change_Texts_Obj() {
		return WPFA_Change_Texts::get_instance();
	}

}
WPFA_Change_Texts_Obj();
