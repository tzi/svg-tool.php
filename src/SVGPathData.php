<?php

namespace SVGTool;

class SVGPathData
{
    public $command;
    public $coordinatesList = [];

    public function fromCommand($command, $coordinatesList = [])
    {
        $this->command = $command;
        $this->coordinatesList = $coordinatesList;

        return $this;
    }
}