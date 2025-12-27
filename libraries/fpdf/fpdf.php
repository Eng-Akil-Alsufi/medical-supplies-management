<?php
// =====================================================
// مكتبة FPDF - مكتبة بسيطة لإنشاء ملفات PDF
// =====================================================

class FPDF {
    private $orientation;
    private $unit;
    private $format;
    private $w;
    private $h;
    private $x;
    private $y;
    private $lMargin;
    private $tMargin;
    private $rMargin;
    private $bMargin;
    private $fontSize;
    private $page;
    private $pages;
    private $pageSizes;
    private $currentFont;
    private $underline;
    private $textColor;
    private $fillColor;
    private $drawColor;
    private $lineWidth;
    private $buffer;
    
    public function __construct($orientation='P', $unit='mm', $format='A4') {
        $this->orientation = $orientation;
        $this->unit = $unit;
        $this->format = $format;
        $this->x = 0;
        $this->y = 0;
        $this->fontSize = 12;
        $this->page = 0;
        $this->pages = array();
        $this->lMargin = 10;
        $this->tMargin = 10;
        $this->rMargin = 10;
        $this->bMargin = 10;
        $this->currentFont = '';
        $this->underline = false;
        $this->textColor = array(0, 0, 0);
        $this->fillColor = array(255, 255, 255);
        $this->drawColor = array(0, 0, 0);
        $this->lineWidth = 0.2;
        $this->buffer = '';
        
        $this->pageSizes = array(
            'A4' => array(210, 297),
            'A3' => array(297, 420),
            'Letter' => array(216, 279)
        );
        
        if (isset($this->pageSizes[$format])) {
            list($this->w, $this->h) = $this->pageSizes[$format];
        } else {
            $this->w = 210;
            $this->h = 297;
        }
        
        if ($orientation === 'L') {
            $temp = $this->w;
            $this->w = $this->h;
            $this->h = $temp;
        }
        
        $this->addPage();
    }
    
    public function addPage($orientation='') {
        $this->page++;
        $this->pages[$this->page] = '';
        
        if ($orientation === 'L') {
            $this->w = 297;
            $this->h = 210;
        } elseif ($orientation === 'P') {
            $this->w = 210;
            $this->h = 297;
        }
        
        $this->x = $this->lMargin;
        $this->y = $this->tMargin;
    }
    
    public function setFont($family, $style='', $size=0) {
        $this->currentFont = $family;
        if ($size > 0) {
            $this->fontSize = $size;
        }
    }
    
    public function setFontSize($size) {
        $this->fontSize = $size;
    }
    
    public function setTextColor($r, $g=0, $b=0) {
        $this->textColor = array($r, $g, $b);
    }
    
    public function setDrawColor($r, $g=0, $b=0) {
        $this->drawColor = array($r, $g, $b);
    }
    
    public function setFillColor($r, $g=0, $b=0) {
        $this->fillColor = array($r, $g, $b);
    }
    
    public function setLineWidth($width) {
        $this->lineWidth = $width;
    }
    
    public function cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false) {
        if ($this->y + $h > $this->h - $this->bMargin) {
            $this->addPage();
        }
        
        $this->pages[$this->page] .= sprintf('BT /F1 %.2f Tf %.2f %.2f Td (%s) Tj ET', 
            $this->fontSize,
            $this->x * 2.834645669,
            (297 - $this->y - $h) * 2.834645669,
            addslashes($txt)
        ) . "\n";
        
        if ($ln == 0) {
            $this->x += $w;
        } elseif ($ln == 1) {
            $this->x = $this->lMargin;
            $this->y += $h;
        } elseif ($ln == 2) {
            $this->x = $this->lMargin;
            $this->y += $h;
        }
    }
    
    public function ln($h=0) {
        $this->x = $this->lMargin;
        $this->y += $h > 0 ? $h : $this->fontSize;
    }
    
    public function output($dest='') {
        // معالجة بسيطة - إرجاع محتوى النص
        $output = "%%PDF-1.4\n";
        foreach ($this->pages as $page) {
            $output .= $page;
        }
        
        if ($dest === 'D') {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="report.pdf"');
        }
        
        return $output;
    }
}
?>
