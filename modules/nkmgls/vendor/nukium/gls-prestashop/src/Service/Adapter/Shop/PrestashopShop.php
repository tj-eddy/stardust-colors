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

namespace Nukium\PrestaShop\GLS\Service\Adapter\Shop;

use Nukium\GLS\Common\Service\Adapter\Shop\ShopInterface;
use Nukium\GLS\Common\Service\Handler\DTO\Adapter\Address\AddressHandler;
use Nukium\GLS\Common\Service\Handler\DTO\Adapter\Address\AddressHandlerFactory;

class PrestashopShop implements ShopInterface
{
    private static $instance = null;

    protected $context;

    protected $addressHandler;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self(
                \Context::getContext(),
                AddressHandlerFactory::getInstance()
            );
        }

        return self::$instance;
    }

    public function __construct(
        \Context $context,
        AddressHandler $addressHandler
    ) {
        $this->context = $context;
        $this->addressHandler = $addressHandler;
    }

    public function getAddress()
    {
        $psAddress = $this->context->shop->getAddress();

        return $this->addressHandler->adapt($psAddress);
    }
}
