<?php
/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://wiki.kumbiaphp.com/Licencia
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@kumbiaphp.com so we can send you a copy immediately.
 *
 * @category   KumbiaPHP
 * @package    Helpers
 * @copyright  Copyright (c) 2005 - 2016 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * Helper para Tags Html
 *
 * @category   KumbiaPHP
 * @package    Helpers
 */
class Html
{

    /**
     * Metatags
     *
     * @var array
     */
    protected static $_metatags = array();
    /**
     * Enlaces de head
     *
     * @var array
     */
    protected static $_headLinks = array();

    /**
     * Crea un enlace usando la constante PUBLIC_PATH, para que siempre funcione
     *
     * @example Html::link
     * echo Html::link('controller/action','Enlace')
     * Crea un enlace a ese controller y acción con el nombre Enlace
     *
     * @param string $action Ruta a la acción
     * @param string $text Texto a mostrar
     * @param string|array $attrs Atributos adicionales
     * @return string
     */
    public static function link($action, $text, $attrs = '')
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }
        return '<a href="' . PUBLIC_PATH . "$action\" $attrs >$text</a>";
    }

    /**
     * Crea un enlace a una acción del mismo controller que estemos
     *
     * @example Html::linkAction
     * echo Html::linkAction('accion/','Enlace a la acción del mismo controller')
     *
     * @param string $action
     * @param string $text Texto a mostrar
     * @param string|array $attrs Atributos adicionales
     * @return string
     */
    public static function linkAction($action, $text, $attrs = '')
    {
        $action = Router::get('controller_path') . "/$action";
        return self::link($action, $text, $attrs);
    }

    /**
     * Permite incluir una imagen
     *
     * @param string $src Atributo src
     * @param string $alt Atributo alt
     * @param string|array $attrs Atributos adicionales
     * @return string
     */
    public static function img($src, $alt='', $attrs = '')
    {
        return '<img src="' . PUBLIC_PATH . "img/$src\" alt=\"$alt\" ".Tag::getAttrs($attrs).' />';
    }

    /**
     * Crea un metatag
     *
     * @param string $content contenido del metatag
     * @param string|array $attrs atributos
     */
    public static function meta($content, $attrs = '')
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }

        self::$_metatags[] = array('content' => $content, 'attrs' => $attrs);
    }

    /**
     * Incluye los metatags
     *
     * @return string
     */
    public static function includeMetatags()
    {
        return implode(PHP_EOL, array_unique(self::$_metatags));
    }

    /**
     * Crea una lista a partir de un array
     *
     * @param array $array Array con el contenido del metatag
     * @param string $type por defecto ul, y si no ol
     * @param string|array $attrs atributos
     * @return string
     */
    public static function lists($array, $type = 'ul', $attrs = '')
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }

        $list = "<$type $attrs>" . PHP_EOL;
        foreach ($array as $item) {
            $list .= "<li>$item</li>" . PHP_EOL;
        }
        $list .= "</$type>" . PHP_EOL;

        return $list;
    }

    /**
     * Incluye los CSS
     *
     * @return string
     */
    public static function includeCss()
    {
        $code = '';
        foreach (Tag::getCss() as $css) {
            $code .= '<link href="' . PUBLIC_PATH . "css/{$css['src']}.css\" rel=\"stylesheet\" type=\"text/css\" media=\"{$css['media']}\" />" . PHP_EOL;
        }
        return $code;
    }

    /**
     * Enlaza un recurso externo
     *
     * @param string $href direccion url del recurso a enlazar
     * @param string|array $attrs atributos
     */
    public static function headLink($href, $attrs = '')
    {
        if (is_array($attrs)) {
            $attrs = Tag::getAttrs($attrs);
        }

        self::$_headLinks[] = array('href' => $href, 'attrs' => $attrs);
    }

    /**
     * Enlaza una accion
     *
     * @param string $action ruta de accion
     * @param string|array $attrs atributos
     */
    public static function headLinkAction($action, $attrs = '')
    {
        self::headLink(PUBLIC_PATH . $action, $attrs);
    }

    /**
     * Enlaza un recurso de la aplicacion
     *
     * @param string $resource ubicacion del recurso en public
     * @param string|array $attrs atributos
     */
    public static function headLinkResource($resource, $attrs = '')
    {
        self::headLink(PUBLIC_PATH . $resource, $attrs);
    }

    /**
     * Incluye los links para el head
     *
     * @return string
     */
    public static function includeHeadLinks()
    {
        $code = '';
        foreach (self::$_headLinks as $link) {
            $code .= "<link href=\"{$link['href']}\" {$link['attrs']} />" . PHP_EOL;
        }
        return $code;
    }

    /**
     * Incluye imágenes de gravatar.com
     *
     * Ejemplos: Html::gravatar
     * Simple: echo Html::gravatar( $email ) <br>
     * Completo: echo Html::gravatar( $email, $name, 20, 'http://www.example.com/default.jpg') <br>
     * Un gravatar que es un link: echo Html::link( Html::gravatar($email), $url)
     *
     * @param string $email Correo para conseguir su gravatar
     * @param string $alt Texto alternativo de la imagen. Por defecto: gravatar
     * @param int $size Tamaño del gravatar. Un número de 1 a 512. Por defecto: 40
     * @param string $default URL gravatar por defecto si no existe, o un default de gravatar. Por defecto: mm
     * @return string
     */
    public static function gravatar($email, $alt='gravatar', $size=40, $default='mm')
    {
        $grav_url = "http://www.gravatar.com/avatar/" . md5(strtolower(trim($email))) . '?d=' . urlencode($default) . '&s=' . $size;
        return '<img src="' . $grav_url . '" alt="' . $alt . '" class="avatar" width="' . $size . '" height="' . $size . '" />';
    }

}
