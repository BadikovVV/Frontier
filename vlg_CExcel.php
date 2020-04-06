<?php
//require_once "PHPExcel/Classes/PHPExcel.php";
//require_once 'vlg_util.php';
////////////////////////////////////////////////////////////////////////////////
// vvv Class CExcel vvv
class CExcel{
    var $excel;
    var $aSheet;
    var $filename;
    var $cRow;
    var $cCol;
    var $cellStyle;
//
    function __construct($filename)
    {
        $this->filename = $filename;
        $this->excel = new PHPExcel();
        $this->excel->setActiveSheetIndex(0);
        $this->aSheet = $this->excel->getActiveSheet();
        $this->cCol=0;
        $this->cRow=1;
        $this->cellStyle=false;
    }
// изменение €чейки ($col, $row)
    function scVal($col, $row, $type, $val) {
        //d($val);
        $this->cCol=$col;
        $this->cRow=$row;
        $this->setCellVal($this->cCol, $this->cRow, $type, $val);
        return true;
    }
// выгрузка массива значений в $row-строку Excel, начина€ с $col
    function sACVal($col, $row, $type, $arrVal) {
        //d($val);
        $this->cCol=$col;
        $this->cRow=$row;
        foreach ($arrVal as &$value){
            $this->setCellVal($this->cCol, $this->cRow, $type, $value);
            $this->cCol++;
        }
        return true;
    }
// изменение €чейки ($this->cCol++, $this->cRow)
    function sncVal($type, $val) {
        $this->cCol++;
        $this->setCellVal($this->cCol, $this->cRow, $type, $val);
        return true;
    }
// изменение €чейки ($col, $row)
    function setCellVal($col, $row, $type, $val) {
        if($this->cellStyle)   $this->aSheet->getStyleByColumnAndRow($col, $row)->applyFromArray($this->cellStyle);
        switch ($type) {
            case 'trimConvString':
                $this->aSheet->setCellValueByColumnAndRow($col,$row,iconv('CP1251', 'UTF-8',trim($val)));
                break;
            case 'String':
                $this->aSheet->setCellValueByColumnAndRow($col,$row,$val);
                break;
            case 'Number':
                break;
            case 'Date':
                //$val = $sheet->getCellByColumnAndRow($col, $row)->getValue();
                //$this->aSheet->getStyleByColumnAndRow($col,$row)->getAlignment()->
                //setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                break;
        }
        return true;
    }    
// сохранение объекта PHPExcel в файл
    function save() {
        //d($this->filename);
        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
        $objWriter->save($this->filename);
        return true;
    }
}
// ^^^ Class CExcel ^^^
////////////////////////////////////////////////////////////////////////////////
?>