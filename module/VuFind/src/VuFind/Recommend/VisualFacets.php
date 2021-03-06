<?php
/**
 * VisualFacets Recommendations Module
 *
 * PHP version 5
 *
 * Copyright (C) Julia Bauder 2014.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category VuFind2
 * @package  Recommendations
 * @author   Julia Bauder <bauderj@grinnell.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:recommendation_modules Wiki
 */
namespace VuFind\Recommend;

/**
 * VisualFacets Recommendations Module
 *
 * This class supports visualizing pivot facet information as a treemap or circle
 * packing visualization.
 *
 * It must be used in combination with a template file including the necessary
 * Javascript in order to display the visualization to the user.
 *
 * @category VuFind2
 * @package  Recommendations
 * @author   Julia Bauder <bauderj@grinnell.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:recommendation_modules Wiki
 */
class VisualFacets extends AbstractFacets
{
    /**
     * Facet configuration
     *
     * @var string
     */
    protected $facets;

    /**
     * setConfig
     *
     * Store the configuration of the recommendation module.
     *
     * VisualFacets:[ini section]:[ini name]
     *      Display facets listed in the specified section of the specified ini file;
     *      if [ini name] is left out, it defaults to "facets."
     *
     * @param string $settings Settings from searches.ini.
     *
     * @return void
     */
    public function setConfig($settings)
    {
        $settings = explode(':', $settings);
        $mainSection = empty($settings[0]) ? 'Visual_Settings':$settings[0];
        $iniName = isset($settings[1]) ? $settings[1] : 'facets';

        // Load the desired facet information:
        $config = $this->configLoader->get($iniName);
        $this->facets = isset($config->$mainSection->visual_facets)
            ? $config->$mainSection->visual_facets : 'callnumber-first,topic_facet';
    }

    /**
     * init
     *
     * @param \VuFind\Search\Base\Params $params  Search parameter object
     * @param \Zend\StdLib\Parameters    $request Parameter object representing user
     * request.
     *
     * @return void
     */
    public function init($params, $request)
    {
        // Turn on pivot facets:
        $params->setPivotFacets($this->facets);
    }

    /**
     * Get facet information taken from the search.
     *
     * @return array
     */
    public function getPivotFacetSet()
    {
        // Avoid fatal error in case of unexpected results object (e.g. EmptySet):
        return is_callable(array($this->results, 'getPivotFacetList'))
            ? $this->results->getPivotFacetList() : array();
    }
}
