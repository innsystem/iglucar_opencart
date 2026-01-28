<?php
class ModelInformationManual extends Model {
    public function getManualInfo() {
        $manual_info = array();
        
        $manual_info['title'] = 'Manual de Uso';
        $manual_info['description'] = 'Manual completo de utilização do sistema';
        $manual_info['file_path'] = 'image/catalog/docs/manual.pdf';
        
        return $manual_info;
    }
}
