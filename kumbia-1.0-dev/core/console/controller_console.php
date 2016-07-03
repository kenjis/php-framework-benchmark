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
 * @category   Kumbia
 * @package    Console
 * @copyright  Copyright (c) 2005 - 2016 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * Consola para manejar controladores
 *
 * @category   Kumbia
 * @package    Console
 */
class ControllerConsole
{

    /**
     * Comando de consola para crear un controlador
     *
     * @param array $params parametros nombrados de la consola
     * @param string $controller controlador
     * @throw KumbiaException
     */
    public function create($params, $controller)
    {
        // nombre de archivo
        $file = APP_PATH . 'controllers';

        // limpia el path de controller
        $clean_path = trim($controller, '/');

        // obtiene el path
        $path = explode('/', $clean_path);

        // obtiene el nombre de controlador
        $controller_name = array_pop($path);

        // si se agrupa el controlador en un directorio
        if (count($path)) {
            $dir = implode('/', $path);
            $file .= "/$dir";
            if (!is_dir($file) && !FileUtil::mkdir($file)) {
                throw new KumbiaException("No se ha logrado crear el directorio \"$file\"");
            }
        }
        $file .= "/{$controller_name}_controller.php";

        // si no existe o se sobreescribe
        if (!is_file($file) ||
                Console::input("El controlador existe, ¿desea sobrescribirlo? (s/n): ", array('s', 'n')) == 's') {

            // nombre de clase
            $class = Util::camelcase($controller_name);

            // codigo de controlador
            ob_start();
            include __DIR__ . '/generators/controller.php';
            $code = '<?php' . PHP_EOL . ob_get_clean();

            // genera el archivo
            if (file_put_contents($file, $code)) {
                echo "-> Creado controlador $controller_name en: $file" . PHP_EOL;
            } else {
                throw new KumbiaException("No se ha logrado crear el archivo \"$file\"");
            }

            // directorio para vistas
            $views_dir = APP_PATH . "views/$clean_path";

            //si el directorio no existe
            if (!is_dir($views_dir)) {
                if (FileUtil::mkdir($views_dir)) {
                    echo "-> Creado directorio para vistas: $views_dir" . PHP_EOL;
                } else {
                    throw new KumbiaException("No se ha logrado crear el directorio \"$views_dir\"");
                }
            }
        }
    }

    /**
     * Comando de consola para eliminar un controlador
     *
     * @param array $params parametros nombrados de la consola
     * @param string $controller controlador
     * @throw KumbiaException
     */
    public function delete($params, $controller)
    {
        // path limpio al controlador
        $clean_path = trim($controller, '/');

        // nombre de archivo
        $file = APP_PATH . "controllers/$clean_path";

        // si es un directorio
        if (is_dir($file)) {
            $success = FileUtil::rmdir($file);
        } else {
            // entonces es un archivo
            $file = "{$file}_controller.php";
            $success = unlink($file);
        }

        // mensaje
        if ($success) {
            echo "-> Eliminado: $file" . PHP_EOL;
        } else {
            throw new KumbiaException("No se ha logrado eliminar \"$file\"");
        }

        // directorio para vistas
        $views_dir = APP_PATH . "views/$clean_path";

        // intenta eliminar el directorio de vistas
        if (is_dir($views_dir)
                && Console::input('¿Desea eliminar el directorio de vistas? (s/n): ', array('s', 'n')) == 's') {

            if (!FileUtil::rmdir($views_dir)) {
                throw new KumbiaException("No se ha logrado eliminar \"$views_dir\"");
            }

            echo "-> Eliminado: $views_dir" . PHP_EOL;
        }
    }

}