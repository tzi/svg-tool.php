<?php

namespace SVGTool;

class SVGPath
{
    const PATH_REGEX_PARSER = '/([a-zA-Z])(?:\s?([0-9\.\-]+)[\s,]([0-9\.\-]+))?/';

    public $pathDataList = [];

    public function fromString($string)
    {
        $matches = [];
        preg_match_all(static::PATH_REGEX_PARSER, $string, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $coordinatesList = [];
            if (count($match) > 2) {
                $coordinatesList[] = [$match[2], $match[3]];
            }
            $this->pathDataList[] = (new SVGPathData)->fromCommand($match[1], $coordinatesList);
        }

        return $this;
    }

    public function toString()
    {
        $pathDataStringList = [];
        foreach ($this->pathDataList as $pathData) {
            $pathDataString = $pathData->command;
            foreach ($pathData->coordinatesList as $coordinates) {
                $pathDataString .= $coordinates[0] . ' ' . $coordinates[1];
            }
            $pathDataStringList[] = $pathDataString;
        }
        return implode(' ', $pathDataStringList);
    }

    public function getViewBox()
    {
        if (!count($this->pathDataList)) {
            return false;
        }
        $min = $this->pathDataList[0]->coordinatesList[0];
        $max = $this->pathDataList[0]->coordinatesList[0];
        foreach ($this->pathDataList as $pathData) {
            foreach ($pathData->coordinatesList as $coordinates) {
                if ($coordinates[0] < $min[0]) $min[0] = $coordinates[0];
                if ($coordinates[0] > $max[0]) $max[0] = $coordinates[0];
                if ($coordinates[1] < $min[1]) $min[1] = $coordinates[1];
                if ($coordinates[1] > $max[1]) $max[1] = $coordinates[1];
            }
        }

        return [$min, $max];
    }

    public function move($deltaX, $deltaY)
    {
        foreach ($this->pathDataList as $pathData) {
            foreach ($pathData->coordinatesList as $index => $coordinate) {
                $pathData->coordinatesList[$index][0] += $deltaX;
                $pathData->coordinatesList[$index][1] += $deltaY;
            }
        }

        return $this;
    }

    public function scale($ratio)
    {
        foreach ($this->pathDataList as $pathData) {
            foreach ($pathData->coordinatesList as $index => $coordinate) {
                $pathData->coordinatesList[$index][0] *= $ratio;
                $pathData->coordinatesList[$index][1] *= $ratio;
            }
        }

        return $this;
    }

    public function center($width, $height)
    {
        $viewBox = $this->getViewBox();
        $minX = $viewBox[0][0];
        $minY = $viewBox[0][1];
        $maxX = $viewBox[1][0];
        $maxY = $viewBox[1][1];
        $deltaX = (($width - ($maxX - $minX)) / 2) - $minX;
        $deltaY = (($height - ($maxY - $minY)) / 2) - $minY;
        $this->move($deltaX, $deltaY);

        return $this;
    }

    public function fit($width, $height, $padding = 0)
    {
        $viewBox = $this->getViewBox();
        $minX = $viewBox[0][0];
        $minY = $viewBox[0][1];
        $maxX = $viewBox[1][0];
        $maxY = $viewBox[1][1];
        $ratioX = ($width - 2 * $padding) / ($maxX - $minX);
        $ratioY = ($height - 2 * $padding) / ($maxY - $minY);
        $ratio = min($ratioX, $ratioY);

        $this->move(-$minX, -$minY)->scale($ratio)->center($width, $height);

        return $this;
    }
}