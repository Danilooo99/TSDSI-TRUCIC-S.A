<?php

namespace Weglot\Parser\Formatter;

use WGSimpleHtmlDom\simple_html_dom;
use Weglot\Parser\Parser;

class CustomSwitchersFormatter
{
    /**
     * @var simple_html_dom
     */
    protected $dom;

    /**
     * @var array
     */
    protected $customSwitchers;

    /**
     * CustomSwitchersFormatter constructor.
     * @param $dom
     */
    public function __construct($dom, $customSwitchers)
    {
        $this
            ->setDom($dom)
            ->setCustomSwitchers($customSwitchers);
        $this->handle($this->dom, $customSwitchers);
    }

    /**
     * @param simple_html_dom $dom
     * @return $this
     */
    public function setDom(simple_html_dom $dom)
    {
        $this->dom = $dom;

        return $this;
    }

    /**
     * @return simple_html_dom
     */
    public function getDom()
    {
        return $this->dom;
    }

    /**
     * @param array $customSwitchers
     * @return $this
     */
    public function setCustomSwitchers(array $customSwitchers)
    {
        $this->customSwitchers = $customSwitchers;

        return $this;
    }

    /**
     * @return array
     */
    public function getCustomSwitchers()
    {
        return $this->customSwitchers;
    }

    /**
     * <div class="target">target</div> foreach customswitchers
     * wanna be translated.
     *
     * @return simple_html_dom
     */
    public function handle($dom, $switchers)
    {
        foreach ($switchers as $switcher) {

            $location = $switcher['location'];
            if ( ! empty( $location ) ) {
                if ( $dom->find( $location['target'] ) && is_array( $dom->find( $location['target'] ) ) ) {
                    foreach ( $dom->find( $location['target'] ) as $target ) {
                        if ( empty( $location['sibling'] ) ) {
                            $target->innertext .= '<div data-wg-position="'.$location['target'].'"></div>';
                        } else {
                            if ( $target->find( $location['sibling'] ) ) {
                                foreach ( $target->find( $location['sibling'], 0 ) as $sibling ) {
                                    if ( is_object( $sibling ) ) {
                                        $sibling->innertext = '<div data-wg-position="'.$location['target'].' '.$location['sibling'].'"></div>' . $sibling->innertext;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $dom;
    }
}
