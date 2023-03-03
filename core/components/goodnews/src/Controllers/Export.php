<?php

/**
 * This file is part of the GoodNews package.
 *
 * @copyright bitego (Martin Gartner)
 * @license GNU General Public License v2.0 (and later)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bitego\GoodNews\Controllers;

use MODX\Revolution\modX;
use Bitego\GoodNews\Controllers\Base;

/**
 * Export manager controller class.
 *
 * @param \MODX\Revolution\modX &$modx A reference to the modX object
 * @param array $config An array of configuration options
 * @extends Bitego\GoodNews\Controllers\Base
 * @package goodnews
 * @subpackage controllers
 */
class Export extends Base
{
    /**
     * {@inheritDoc}
     *
     * @access public
     * @return mixed
     */
    public function initialize()
    {
        if (!$this->goodnews->isGoodNewsAdmin) {
            $returl = $this->modx->getOption('manager_url') . '?a=' . $_GET['a'];
            $this->modx->sendRedirect($returl);
        }
        parent::initialize();
    }

    /**
     * {@inheritDoc}
     *
     * @access public
     * @return string
     */
    public function render(): string
    {
        $this->failure('');

        $this->loadHeader = false;
        $this->loadFooter = false;
        $this->content = '';

        $currentTime = time();
        $exportDir = $this->modx->getOption('core_path', null, MODX_CORE_PATH) . 'cache/goodnews/tmp/';
        $exportFilePath = $exportDir . $_GET['f'];
        $exportFileSize = filesize($exportFilePath);
        $newFileName = 'subscribers.csv';

        // If exists, push file to browser
        if (file_exists($exportFilePath)) {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment;filename="' . $newFileName  . '";');
            header("Content-length: " . $exportFileSize);

            //No cache!
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');

            if (ob_get_length() > 0) {
                ob_clean();
            }
            flush();
            readfile($exportFilePath);

            // Delete temporary file
            unlink($exportFilePath);

            exit();
        }

        // If file is missing, render error page
        $this->modx->log(
            modX::LOG_LEVEL_ERROR,
            '[GoodNews] GoodNewsExportManagerController: Temporary download file not found in path: ' .
            $exportFilePath  .
            '! Processing aborted.'
        );

        $this->content =
        '<!DOCTYPE html>
        <html>
            <head>
                <meta charset="utf-8">
                <title>' .
                    $this->modx->lexicon('goodnews') . ': ' . $this->modx->lexicon('goodnews.export_subscribers') .
                '</title>
            </head>
            <body style="font-family: Sans-Serif;">
                <h1 style="text-align: center; margin-top: 50px; margin-bottom: 10px; font-size: 24px;">' .
                    $this->modx->lexicon('goodnews.export_subscribers_error_page_ns_export_file') .
                '</h1>
                <p style="text-align: center; font-size: 15px; margin-bottom: 30px;">' .
                    $this->modx->lexicon('goodnews.export_subscribers_error_page_check_log') .
                '</p>
                <p style="text-align: center;">
                    <button onClick="history.go(-1); return false;" style="font-size: 18px;">' .
                        $this->modx->lexicon('goodnews.export_subscribers_error_page_back_button') .
                    '</button>
                </p>
            </body>
        </html>';

        return $this->content;
    }

    /**
     * {@inheritDoc}
     *
     * @access public
     * @return mixed
     */
    public function failure($message)
    {
        $this->isFailure = false;
        $this->failureMessage = '';
    }
}
