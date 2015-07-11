<?php

namespace rock\snippets;

use rock\helpers\Instance;
use rock\image\ImageProvider;
use rock\image\ThumbInterface;
use rock\template\Html;

/**
 * Snippet "Thumb"
 * @package rock\snippets\
 *
 * @see Thumb
 */
class Thumb extends Snippet
{
    /**
     * Src to image.
     * @var string
     */
    public $src;
    /**
     * width
     * @var
     */
    public $w;
    /**
     * height
     * @var
     */
    public $h;
    /**
     * quality
     * @var
     */
    public $q;
    /**
     * attr "class"
     * @var
     */
    public $_class;
    /**
     * attr "alt"
     * @var
     */
    public $alt;
    public $title;
    /** @var  string */
    public $dummy;
    public $const = 1;
    public $autoEscape = false;

    /** @var  ImageProvider|string|array */
    public $imageProvider = 'imageProvider';

    public function init()
    {
        parent::init();
        $this->imageProvider = Instance::ensure($this->imageProvider);
    }

    public function get()
    {
        if (empty($this->src)) {
            if (empty($this->dummy)) {
                return '';
            }
            $this->src = $this->dummy;
        }

        $options = [
            'class' => $this->_class,
            'alt' => $this->template->replace($this->alt),
            'title' => $this->title,
        ];
        $src = $this->imageProvider->get($this->src, $this->w, $this->h);

        if (!((int)$this->const & ThumbInterface::WITHOUT_WIDTH_HEIGHT)) {
            $options['width'] = $this->imageProvider->width;
            $options['height'] = $this->imageProvider->height;
        }

        return (int)$this->const & ThumbInterface::OUTPUT_IMG ? Html::img($src, $options) : $src;
    }
}