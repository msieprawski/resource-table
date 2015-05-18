<?php namespace Msieprawski\ResourceTable\Presenters;

/**
 * Admin LTE admin template pagination presenter
 * https://almsaeedstudio.com/AdminLTE
 *
 * @package Msieprawski\ResourceTable\Presenters
 */
class AdminLTEPresenter extends DefaultPresenter
{
    /**
     * Convert the URL window into Bootstrap HTML
     *
     * @return string
     */
    public function render()
    {
        if ($this->hasPages())
        {
            return sprintf(
                '<ul class="pagination pagination-sm no-margin pull-right">%s %s %s</ul>',
                $this->getPreviousButton(),
                $this->getLinks(),
                $this->getNextButton()
            );
        }

        return '';
    }

    /**
     * Get HTML wrapper for disabled text
     *
     * @param string $text
     * @return string
     */
    protected function getDisabledTextWrapper($text)
    {
        return '<li class="disabled"><a href="#">'.$text.'</a></li>';
    }

    /**
     * Get HTML wrapper for active text
     *
     * @param string $text
     * @return string
     */
    protected function getActivePageWrapper($text)
    {
        return '<li class="active"><a href="#">'.$text.'</a></li>';
    }
}
