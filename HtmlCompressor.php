<?php

namespace zabachok\htmlcompressor;

/**
 * Class HtmlCompressor
 * @package zabachok\htmlcompressor
 * @author Daniil Romanov <zabachok@zabachok.net>
 */
class HtmlCompressor
{

    /**
     * Content for compression
     *
     * @var string
     */
    public $content = '';

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

    /**
     * Type of part of content
     * @var string
     */
    private $state = 'html';

    /**
     * Memory for detect pattern
     * @var string
     */
    private $word = '';

    /**
     * Pattern with their types
     * @var array
     */
    private $patterns = [
        'start' => [
            '<code' => 'code',
            '<script' => 'script',
            '<textarea'=>'textarea',
        ],
        'end' => [
            '</code>' => 'code',
            '</script>' => 'script',
            '</textarea>' => 'textarea',
        ],
    ];

    /**
     * Exploded content by type
     * @var array
     */
    private $data = [];

    /**
     * Part of content
     * @var string
     */
    private $part = '';

    /**
     * HtmlCompressor constructor. Needs to load settings.
     * @param bool $compressScript
     * @param bool $compressCode
     */
    public function __construct($compressScript = true, $compressCode = true)
    {
        $this->compressScript = $compressScript;
        $this->compressCode = $compressCode;
    }

    /**
     * Method to compress code html
     *
     * @param string $content String needs to compress
     * @return string Result of compression
     */
    public function make($content)
    {
        $this->content = $content;
        $this->explode();

        if (empty($this->data))
        {
            return $this->compress($this->content);
        }
        else
        {
            $output = '';
            foreach ($this->data as $item)
            {
                $compress = false;
                if ($item['type'] == 'html') $compress = true;
                if ($item['type'] == 'code' && $this->compressCode) $compress = true;
                if ($item['type'] == 'script' && $this->compressScript) $compress = true;
                if ($compress)
                {
                    $output .= self::compress($item['content']);
                }
                else $output .= $item['content'];
            }
            return $output;
        }
    }

    /**
     * Exploding content by types
     */
    private function explode()
    {
//        if ($this->compressScript && $this->compressCode) return;
        for ($i = 0; $i < strlen($this->content); $i++)
        {
            $char = $this->content{$i};
            $this->readChar($char);
        }

        $this->part = '';
    }

    /**
     * Pattern detector
     * @param string $char
     */
    private function readChar($char)
    {
        $this->word .= $char;
        $this->part .= $char;
        $tail = $this->state == 'html' ? 'start' : 'end';
        $hasStart = false;
        $full = false;
        foreach (array_keys($this->patterns[$tail]) as $pattern)
        {
            if (strpos($pattern, $this->word) === 0)
            {
                $hasStart = true;
                if ($pattern == $this->word)
                {
                    $full = true;
                    break;
                }
            }
        }
        if ($hasStart == false)
        {
            $this->word = '';
        }
        if ($full == true)
        {
            $this->startNew($this->patterns[$tail][$pattern]);
            if ($tail == 'end') $this->state = 'html';
        }
    }

    /**
     *
     *
     * @param string $state
     */
    private function startNew($state)
    {
        $this->data[] = [
            'type' => $this->state,
            'content' => $this->part,
        ];
        $this->state = $state;
        $this->part = '';
        $this->word = '';
    }


    /**
     * HTML compress function.
     * You can use it without other methods of class.
     * `$result = HtmlCompressor::compress($myHtml);`
     *
     * @param $input
     * @return string Result of compress
     */
    public static function compress($input)
    {
        $filters = array(
            // remove HTML comments except IE conditions
            '/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s' => '',
            // remove comments in the form /* */
            '/\/+?\s*\*[\s\S]*?\*\s*\/+/' => '',
            //remove js inline comments
            '/\/\/.*\r?\n/' => '',
            // shorten multiple white spaces
            '/>\s{2,}</' => '><',
            // shorten multiple white spaces
            '/\s{2,}/' => ' ',
            // collapse new lines
            '/(\r?\n)/' => '',
        );

        $output = preg_replace(array_keys($filters), array_values($filters), $input);
        return $output;
    }
}