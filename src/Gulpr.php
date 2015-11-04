<?php

namespace Zuccadev\Gulpr;

use Illuminate\Support\Collection;

/**
 * Class Gulpr
 * @package App\Gulpr
 */
class Gulpr
{
    /**
     * @var mixed
     */
    protected $config;
    /**
     * @var static
     */
    protected $styles;
    /**
     * @var static
     */
    protected $scripts;
    /**
     * @var bool
     */
    protected $cacheBust;

    /**
     *
     */
    public function __construct()
    {
        $configFile = app_path('../assets.json');
        $this->config = json_decode(file_get_contents($configFile));

        $this->styles = Collection::make($this->config->styles);
        $this->scripts = Collection::make($this->config->scripts);

        $this->cacheBust = $this->getCacheBustHash();
    }

    /**
     * @param $key
     * @return string
     */
    public function styles($key)
    {
        if ($this->styles->has($key)) {

            $styles = new Collection;

            if ($this->isOptimized()) {
                $styles->push($this->getStylePath(null, $key));
            } else {
                $files = Collection::make($this->styles->get($key)->files);

                $files->each(function ($item) use ($key, $styles) {
                    $path = is_object($item) ? "{$item->dest->path}/{$item->dest->filename}" : $item;
                    $styles->push($this->getStylePath($path, $key));
                });
            }

            $html = '';

            $styles->each(function ($item) use (&$html) {
                $html .= $this->renderStyleTag($item);
            });

            return $html;
        }
    }

    /**
     * @param $key
     * @return string
     */
    public function scripts($key)
    {
        if ($this->scripts->has($key)) {

            $scripts = new Collection;

            if ($this->isOptimized()) {
                $scripts->push($this->getScriptPath(null, $key));
            } else {
                $files = Collection::make($this->scripts->get($key)->files);

                $files->each(function ($path) use ($key, $scripts) {
                    $scripts->push($this->getScriptPath($path, $key));
                });
            }

            $html = '';

            $scripts->each(function ($item) use (&$html) {
                $html .= $this->renderScriptTag($item);
            });

            return $html;
        }
    }

    /**
     * @param $filePath
     * @param $key
     * @return string
     */
    protected function getStylePath($filePath, $key)
    {
        return $this->getFilePath('styles', $filePath, $key);
    }

    /**
     * @param $filePath
     * @param $key
     * @return string
     */
    protected function getScriptPath($filePath, $key)
    {
        return $this->getFilePath('scripts', $filePath, $key);
    }

    /**
     * @param $type
     * @param $key
     * @return string
     */
    protected function getOptimizedFilePath($type, $key)
    {
        $base = $this->config->dist->base;

        $path = "{$base}/{$this->config->dist->$type}";

        $ext = $type === 'scripts' ? 'js' : 'css';

        return $this->isDebug() ? "{$path}/{$key}.{$ext}" : "{$path}/{$key}.min.{$ext}";
    }

    /**
     * @param $type
     * @param $path
     * @param $key
     * @return string
     */
    protected function getFilePath($type, $path, $key)
    {
        if ($this->isOptimized()) {
            return $this->getOptimizedFilePath($type, $key);
        }

        return $path;
    }

    /**
     * @return bool
     */
    protected function isOptimized()
    {
        $env = app()->environment();

        if ($env !== 'local') return true;

        return false;
    }

    /**
     * @return mixed
     */
    protected function isDebug()
    {
        return config('app.debug');
    }

    /**
     * @return bool
     */
    protected function getCacheBustHash()
    {
        $storage = app()->make('filesystem');

        $fileName = 'cacheBust.json';

        if ($storage->disk('public')->exists($fileName)) {
            $hash = $storage->disk('public')->get($fileName);

            return json_decode($hash)->value;
        }

        return false;
    }

    /**
     * @param $item
     * @return string
     */
    protected function renderStyleTag($item)
    {
        $uri = $this->getResourceUrl($item);

        return "<link href=\"{$uri}\" type=\"text/css\" rel=\"stylesheet\">\n";
    }

    /**
     * @param $item
     * @return string
     */
    protected function renderScriptTag($item)
    {
        $uri = $this->getResourceUrl($item);

        return "<script src=\"{$uri}\"></script>\n";
    }

    /**
     * @param $item
     * @return string
     */
    protected function getCacheBustUrl($item)
    {
        if (!$this->cacheBust) return asset($item);

        return asset($item) . '?cb=' . $this->cacheBust;
    }

    /**
     * @param $item
     * @return string
     */
    protected function getResourceUrl($item)
    {
        $uri = $item;

        if ($this->isOptimized() && $this->config->dist->cacheBust) {
            $uri = $this->getCacheBustUrl($item);
            return $uri;
        }
        return asset($uri);
    }
}