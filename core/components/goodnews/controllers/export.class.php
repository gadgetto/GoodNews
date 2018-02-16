<?php
/**
 * GoodNews
 *
 * Copyright 2012 by bitego <office@bitego.com>
 *
 * GoodNews is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * GoodNews is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this software; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 */

/**
 * GoodNews Export manager controller
 * (This doesn't render a manager page but pushing a file to browser for download.)
 *
 * @package goodnews
 */

class GoodNewsExportManagerController extends GoodNewsManagerController {

    public function process(array $scriptProperties = array()) {}
    
	public function render(){
        $this->modx->lexicon->load('goodnews:default');

        $this->failure('');

        $this->loadHeader = false;
        $this->loadFooter = false;
        $this->content = '';
        
        $currentTime = time();
        $exportDir = $this->modx->getOption('core_path', null, MODX_CORE_PATH).'cache/goodnews/tmp/';
        $exportFilePath = $exportDir.$_GET['f'];
        $exportFileSize = filesize($exportFilePath);
        $newFileName = 'subscribers.csv';

        // If exists, push file to browser
        if (file_exists($exportFilePath)) {

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment;filename="'.$newFileName.'";');
            header("Content-length: ".$exportFileSize);

            //No cache!
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');

            if (ob_get_length() > 0) { ob_clean(); }
            flush();
            readfile($exportFilePath);
            
            // Delete temporary file
            unlink($exportFilePath);
            
            exit();
        }

        // If file is missing, render error page
        $this->modx->log(modX::LOG_LEVEL_ERROR,'[GoodNews] GoodNewsExportManagerController: Temporary download file not found in path: '.$exportFilePath.'! Processing aborted.');
        
        $this->content = '<html><title>'.$this->modx->lexicon('goodnews').': '.$this->modx->lexicon('goodnews.export_subscribers').'</title><head></head><body style="font-family: Sans-Serif;">
        <h1 style="text-align: center; margin-top: 50px; margin-bottom: 10px; font-size: 24px;">'.$this->modx->lexicon('goodnews.export_subscribers_error_page_ns_export_file').'</h1>
        <p style="text-align: center; font-size: 15px; margin-bottom: 30px;">'.$this->modx->lexicon('goodnews.export_subscribers_error_page_check_log').'</p>
        <p style="text-align: center;"><button onClick="history.go(-1); return false;" style="font-size: 18px;">'.$this->modx->lexicon('goodnews.export_subscribers_error_page_back_button').'</button></p>
        </body></html>';

        return $this->content;
	}
	
	public function failure($message){
		$this->isFailure = false;
		$this->failureMessage = '';	
	}
}
