<?php

namespace App\Livewire;

use Livewire\Component;

class Counter extends Component
{
    public $count = 0;

    // cores
    public $bgColor = '';

    public function increment()
    {
        $this->count += 1;
        $this->updateBgColor();
    }

    public function decrement()
    {
        $this->count -= 1;
        $this->updateBgColor();
    }

    private function updateBgColor()
    {
        if ($this->count >= 5) {
            $this->bgColor = 'bg-success';
        } elseif ($this->count <= -5) {
            $this->bgColor = 'bg-danger';
        } else {
            $this->bgColor = '';
        }
    }

    public function render()
    {
        return view('livewire.counter');
    }
}
