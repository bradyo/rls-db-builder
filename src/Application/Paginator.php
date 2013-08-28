<?php

namespace Application;

class Paginator
{
    private $currentPage;
    private $itemsPerPage;
    private $maxPage;

    private $leftEndPage;
    private $isLeftCollapsed;
    private $middleStartPage;
    private $middleEndPage;
    private $isRightCollapsed;
    private $rightStartPage;
    private $rightEndPage;

    public function __construct($itemsCount, $offset = 0, $itemsPerPage = 20,
            $middleRange = 5, $stubRange = 2) {
        /*
         * What the pagination should look like:
        Prev 1 :2: 3 4 5 6 7 ... 20 21 Next
        Prev 1 2 ... 4 5 6 7 8 :9: 10 11 12 13 14 ... 20 21 Next
        Prev 1 2 ... 15 16 17 18 19 :20: 21 Next
        */

        $this->itemsPerPage = $itemsPerPage;

        $this->maxPage = intval($itemsCount / $itemsPerPage);
        if ($this->maxPage < 1) {
            $this->maxPage = 1;
        }

        $this->currentPage = $offset / $itemsPerPage + 1;
        if ($this->currentPage > $this->maxPage) {
            $this->currentPage = $this->maxPage;
        }

//        if ($pagesCount < 2 * $stubRange + $middleRange) {
            // show full range of pages
            $this->leftEndPage = $this->maxPage;
            $this->isLeftCollapsed = false;
            $this->isRightCollapsed = false;
//        } else {
//            // collapse pages
//
//        }
    }

    public function getRangeStartPage() {
        $rangeStartPage = $this->currentPage - $this->pageRangeDelta;
        if ($rangeStartPage < 1) {
            $rangeStartPage = 1;
        }
        return $rangeStartPage;
    }

    public function getRangeEndPage() {
        $rangeEndPage = $this->currentPage + $this->pageRangeDelta;
        if ($rangeEndPage > $this->getPageCount()) {
            $rangeEndPage = $this->getPageCount();
        }
        if ($rangeEndPage < 2 * $this->pageRangeDelta + 1) {
            $rangeEndPage = 2 * $this->pageRangeDelta + 1;
        }
        if ($rangeEndPage > $this->getPageCount()) {
            $rangeEndPage = $this->getPageCount();
        }
        return $rangeEndPage;
    }

    public function getCurrentPage() {
        return $this->currentPage;
    }

    public function getMaxPage() {
        return $this->maxPage;
    }

    public function getLeftEndPage() {
        return $this->leftEndPage;
    }

    public function getIsLeftCollapsed() {
        return $this->isLeftCollapsed;
    }

    public function getMiddleStartPage() {
        return $this->middleStartPage;
    }

    public function getMiddleEndPage() {
        return $this->middleEndPage;
    }

    public function getIsRightCollapsed() {
        return $this->isRightCollapsed;
    }

    public function getRightStartPage() {
        return $this->rightStartPage;
    }

    public function getRightEndPage() {
        return $this->rightEndPage;
    }

    public function getPageOffset($page) {
        return ($page - 1) * $this->itemsPerPage;
    }
}