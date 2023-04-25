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
class NkmCsv
{
    public $csvDelimeter = "\t";
    public $csvLine = "\r\n";
    public $csvCapsule = '"';

    private $csvTemplate = [];
    private $csvCollection = [];
    private $csvDocument;

    public $reader = null;

    public function __construct()
    {
        $this->reader = new NkmCSVReader();
    }

    public function createTemplate($arr = [])
    {
        $this->csvTemplate = $arr;
    }

    public function addEntry($arr = [])
    {
        foreach ($arr as $Index => $Value) {
            $arr[$Index] = $this->csvCapsule . str_replace($this->csvCapsule, $this->csvCapsule . $this->csvCapsule, $Value) . $this->csvCapsule;
        }
        $this->csvCollection[] = $arr;
    }

    public function buildDoc()
    {
        $docLine = '';
        $csvTemplate = $this->csvTemplate;

        foreach ($csvTemplate as $Index => $Title) {
            $csvTemplate[$Index] = $this->csvCapsule . str_replace($this->csvCapsule, $this->csvCapsule . $this->csvCapsule, $Title) . $this->csvCapsule;
        }
        $docLine .= implode($this->csvDelimeter, $csvTemplate) . $this->csvLine;

        foreach ($this->csvCollection as $csvCollectionItem) {
            $collectionDeposit = [];
            foreach ($csvTemplate as $Index => $Title) {
                $collectionDeposit[] = $csvCollectionItem[$Index];
            }

            $docLine .= implode($this->csvDelimeter, $collectionDeposit) . $this->csvLine;
        }

        return $docLine;
    }
}

class NkmCSVReader
{
    public $fields;

    public $separator = ';';

    public $enclosure = '"';

    public $max_row_size = 20000;

    public function parse_file($p_Filepath, $p_NamedFields = true, $skip_first_line = false)
    {
        $content = false;
        $file = fopen($p_Filepath, 'r');
        if ($p_NamedFields) {
            $this->fields = fgetcsv($file, $this->max_row_size, $this->separator, $this->enclosure);
        }

        $cpt = 0;
        while (($row = fgetcsv($file, $this->max_row_size, $this->separator, $this->enclosure)) != false) {
            ++$cpt;
            if ($skip_first_line && $cpt == 1) {
                continue;
            }

            if ($row[0] != null) {
                if (!$content) {
                    $content = [];
                }
                if ($p_NamedFields) {
                    $items = [];

                    foreach ($this->fields as $id => $field) {
                        if (isset($row[$id])) {
                            $items[$field] = $row[$id];
                        }
                    }
                    $content[] = $items;
                } else {
                    $content[] = $row;
                }
            }
        }
        fclose($file);

        return $content;
    }
}
