<?php

namespace Sensy\Crud\Traits;


use Livewire\WithFileUploads;

trait WizardTrait
{
    use WithFileUploads;

    public $target;
    public $item_id;

    // Steps
    public $step, $total_steps, $reached_step;
    // public $wizardSteps = []; // Use this in component
    // public $wizardIcons = []; // Use this in component

    /**
     * Go to a specific Step
     * @param int $step
     * @return void
     */
    public function specificStep($step)
    {
        if ($step <= $this->reached_step)
        {
            $this->step = $step;
        }
    }

    /**
     * Go to previous Step
     * @return void
     */
    public function previousStep()
    {
        if ($this->step > 1)
        {
            --$this->step;
        }
    }


    /**
     * Go to next Step
     * @return void
     */
    public function goNextStep()
    {
        if ($this->step < $this->total_steps)
        {
            ++$this->step;

            $this->reached_step++;
        }
    }
}
