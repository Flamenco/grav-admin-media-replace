<?php
/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2018 TwelveTone LLC
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Grav\Plugin;

use Grav\Common\Page\Media;
use Grav\Common\Plugin;

function array_get($arr, $key, $default = null)
{
    if (!isset($arr[$key])) {
        if ($default === null) {
            throw new \Exception("A key is missing: " . $key);
        } else {
            return $default;
        }
    }
    return $arr[$key];
}

/**
 * Class AdminMediaReplacePlugin
 * @package Grav\Plugin
 */
class AdminMediaReplacePlugin extends Plugin
{

    const ROUTE = '/admin-media-replace';

    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0]
        ];
    }

    public function onPluginsInitialized()
    {
        if (!$this->isAdmin() || !$this->grav['user']->authenticated) {
            return;
        }

        $this->enable([
            'onTwigTemplatePaths' => ['onTwigTemplatePaths', 0],
            'onPageNotFound' => ['onPageNotFound', 1],
        ]);

        $this->grav['media-actions']->addAction([
            'actionId' => "MediaReplace",
            'caption' => "Replace",
            'icon' => "exchange",
            'handler' => function ($page, $mediaName, $payload) {
                $ret = [
                    "error" => false
                ];
                return $ret;
            }
        ]);
    }

    public function onPageNotFound($e)
    {
        if (!$this->isAdmin()) {
            return;
        }

        $route = $this->grav['admin']->location . "/" . $this->grav['admin']->route;
        switch ($route) {
            case "admin-media-replace/replace":
                try {
                    $filename = array_get($_POST, "media-new-filename");
                    $route = array_get($_POST, "media-route");
                    $media = array_get($_POST, "media-filename");

                    $page = $this->grav['pages']->find($route);
                    if (!$page) {
                        throw new \Exception("Page not found.");
                    }
                    $mediaPath = $page->path() . "/" . $media;
                    if (!is_file($mediaPath)) {
                        throw new \Exception("Media not found.");
                    }

                    $tmp_name = $_FILES['mediaupload']['tmp_name'];
                    move_uploaded_file($tmp_name, $page->path() . '/' . basename($mediaPath));
                    //$tmp_name = $_FILES["pictures"]["tmp_name"][$key];
                    // basename() may prevent filesystem traversal attacks;
                    // further validation/sanitation of the filename may be appropriate
                    //$name = basename($_FILES["pictures"]["name"][$key]);

                    $media1 = new Media($page->path());
                    $medium = $media1[basename($media)];
                    $url = $medium->display($medium->get('extension') === 'svg' ? 'source' : 'thumbnail')->cropZoom(400, 300)->url();
                    die("{\"thumbnail\":\"$url\"}");
                    // Get original name
                    //$source = $medium->higherQualityAlternative()->get('filename');
                    //$media_list[$name] = ['url' => $medium->display($medium->get('extension') === 'svg' ? 'source' : 'thumbnail')->cropZoom(400, 300)->url(), 'size' => $medium->get('size'), 'metadata' => $metadata, 'original' => $source->get('filename')];

                } catch (\Exception $exception) {
//                    die(print_r($_SERVER));
                    $exception = str_replace("\"", "\\\"", $exception);
                    die("{\"error\":\"$exception\"}");
                }
                break;
        }
    }

    public function onTwigTemplatePaths()
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
        $this->grav['assets']->addJs('plugin://admin-media-replace/assets/media_replace_action.js', -1000, false);
    }

    public function outputError($msg)
    {
        header('HTTP/1.1 400 Bad Request');
        die(json_encode(['error' => ['msg' => $msg]]));
    }
}
