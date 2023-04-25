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

namespace Nukium\PrestaShop\GLS\Service\Helper;

use Nukium\GLS\Common\Service\Adapter\Config\ConfigFactory;
use Nukium\PrestaShop\GLS\Service\Adapter\Config\PrestashopConfig;

class ModuleHelper
{
    private static $instance = null;

    protected $context;

    protected $config;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self(
                \Context::getContext(),
                ConfigFactory::getInstance()
            );
        }

        return self::$instance;
    }

    public function __construct(
        \Context $context,
        PrestashopConfig $config
    ) {
        $this->context = $context;
        $this->config = $config;
    }

    public function generateCronUri()
    {
        $cronQuery = [
            'secure_key' => $this->config->get('GLS_SECURE_KEY'),
            'action' => 'get_tracking',
        ];

        if (
            \Shop::isFeatureActive() &&
            \Shop::getContext() === \Shop::CONTEXT_SHOP
        ) {
            $cronQuery['id_shop'] = \Shop::getContextShopID();
        }

        return $this->context->link->getModuleLink(
            'nkmgls',
            'tracking',
            $cronQuery
        );
    }
}
