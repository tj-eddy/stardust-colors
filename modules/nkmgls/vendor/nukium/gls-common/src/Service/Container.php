<?php
/**
 *  Module made by Nukium
 *
 *  @author    Nukium
 *  @copyright 2022 Nukium SAS
 *  @license   All rights reserved
 *
 * ███    ██ ██    ██ ██   ██ ██ ██    ██ ███    ███
 * ████   ██ ██    ██ ██  ██  ██ ██    ██ ████  ████
 * ██ ██  ██ ██    ██ █████   ██ ██    ██ ██ ████ ██
 * ██  ██ ██ ██    ██ ██  ██  ██ ██    ██ ██  ██  ██
 * ██   ████  ██████  ██   ██ ██  ██████  ██      ██
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Nukium\GLS\Common\Service;

use Nukium\GLS\Common\Exception\LogicException;

class Container implements ContainerInterface
{
    private static $instance = null;

    protected $services = [];

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function setInstance(ContainerInterface $container)
    {
        self::$instance = $container;
    }

    public function __construct()
    {
    }

    public function get($classname)
    {
        if (!isset($this->services[$classname])) {
            $reflectionClass = new \ReflectionClass($classname);

            if (!$reflectionClass->isInstantiable()) {
                throw new LogicException("You must provide {$classname} to the Container");
            }

            $this->services[$classname] = $classname::getInstance();
        }

        return $this->services[$classname];
    }

    public function set($classname, $instance)
    {
        $this->services[$classname] = $instance;
    }
}
