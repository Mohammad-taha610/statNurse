<?php

namespace App\Entity\Nst\Member;

class ProviderImportReadFilter
{
    protected $num_rows;

    protected $start_row;

    protected $column_array;

    public function __construct($num_rows, $column_array, $start_row)
    {
        $this->num_rows = $num_rows;
        $this->column_array = $column_array;
        $this->start_row = $start_row;
    }

    public function readCell($column, $row, $worksheetName = '')
    {
        if ($row >= $this->start_row && $row <= $this->num_rows) {
            if (in_array($column, $this->column_array)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  mixed  $num_rows
     */
    public function setNumRows($num_rows): void
    {
        $this->num_rows = $num_rows;
    }

    /**
     * @param  mixed  $start_row
     */
    public function setStartRow($start_row): void
    {
        $this->start_row = $start_row;
    }

    /**
     * @param  mixed  $column_array
     */
    public function setColumnArray($column_array): void
    {
        $this->column_array = $column_array;
    }
}
