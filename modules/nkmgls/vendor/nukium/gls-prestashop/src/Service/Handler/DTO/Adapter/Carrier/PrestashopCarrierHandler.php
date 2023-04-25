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

namespace Nukium\PrestaShop\GLS\Service\Handler\DTO\Adapter\Carrier;

use Nukium\GLS\Common\Service\Handler\DTO\Adapter\Carrier\CarrierHandler;
use Nukium\GLS\Common\Value\GlsValue;
use Nukium\PrestaShop\GLS\Service\Adapter\Config\PrestashopConfig;

class PrestashopCarrierHandler extends CarrierHandler
{
    private static $instance;

    protected $config;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self(
                PrestashopConfig::getInstance()
            );
        }

        return self::$instance;
    }

    public function __construct(
        PrestashopConfig $config
    ) {
        parent::__construct();

        $this->config = $config;
    }

    public function adapt($carrier)
    {
        $idCarrier = (int) $carrier->id;

        $relais = (int) $this->config->get('GLS_GLSRELAIS_ID');
        $chezVous = (int) $this->config->get('GLS_GLSCHEZVOUS_ID');
        $chezVousPlus = (int) $this->config->get('GLS_GLSCHEZVOUSPLUS_ID');
        $avant13h = (int) $this->config->get('GLS_GLS13H_ID');

        $resolve = [
            $relais => GlsValue::GLS_RELAIS,
            $chezVous => GlsValue::GLS_CHEZ_VOUS,
            $chezVousPlus => GlsValue::GLS_CHEZ_VOUS_PLUS,
            $avant13h => GlsValue::GLS_AVANT_13H,
        ];

        $adaptedCarrier = $this->create();

        if (!isset($resolve[$idCarrier])) {
            return $adaptedCarrier
                ->setIsGls(false)
            ;
        }

        return $adaptedCarrier
            ->setIsGls(true)
            ->setCode($resolve[$idCarrier])
        ;
    }
}
