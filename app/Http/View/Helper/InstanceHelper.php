<?php
declare(strict_types = 1);
namespace App\Http\View\Helper;

class InstanceHelper extends ApplicationHelper
{
    /**
     * Creates the little progress bars for resources
     *
     * @param integer $value
     * @return string
     */
    public function resourceProgress(int $value) : string
    {
        $class = null;
        if ($value >= 85) {
            $class = ' bg-danger';
        } elseif ($value >= 75) {
            $class = ' bg-warning';
        }

        return <<< EOF
        <div class="progress">
            <div class="progress-bar{$class}" role="progressbar" style="width: {$value}%" aria-valuenow="{$value}" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        EOF;
    }
}
