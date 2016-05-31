<?php
/**
 * Created by PhpStorm.
 * User: zabachok
 * Date: 31.05.16
 * Time: 14:14
 */

namespace zabachok\htmlcompressor;

use yii\base\Behavior;

class HtmlCompressorBehavior extends Behavior
{
    /**
     * Enable or disable compression, by default compression is enabled.
     *
     * @var bool
     */
    public $compress = true;

    /**
     * Flag to disable code compression in a `script` tag.
     * True by default for increased speed.
     * @var bool
     */
    public $compressScript = true;

    /**
     * Flag to disable code compression in a `code` tag.
     * True by default for increased speed.
     * @var bool
     */
    public $compressCode = true;
    
    public function __construct()
    {
        if ($this->compress === true) {
            \Yii::$app->response->on(\yii\web\Response::EVENT_BEFORE_SEND, function (\yii\base\Event $event) {
                $response = $event->sender;
                if ($response->format === \yii\web\Response::FORMAT_HTML) {
                    if (!empty($response->data)) {
                        $response->data = $this->compress($response->data);
                    }
                    if (!empty($response->content)) {
                        $response->content = $this->compress($response->content);
                    }
                }
            });
        }
    }

    /**
     * HTML compress function.
     *
     * @param $html
     * @return mixed
     */
    private function compress($html)
    {
        $compressor = new HtmlCompressor($this->compressScript, $this->compressCode);
        return $compressor->make($html);
    }
}