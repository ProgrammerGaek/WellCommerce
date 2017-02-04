<?php
/*
 * WellCommerce Open-Source E-Commerce Platform
 *
 * This file is part of the WellCommerce package.
 *
 * (c) Adam Piotrowski <adam@wellcommerce.org>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */
namespace WellCommerce\Bundle\DictionaryBundle\DataGrid;

use WellCommerce\Bundle\AppBundle\DataGrid\AbstractDataGrid;
use WellCommerce\Component\DataGrid\Column\Column;
use WellCommerce\Component\DataGrid\Column\ColumnCollection;
use WellCommerce\Component\DataGrid\Column\ColumnInterface;
use WellCommerce\Component\DataGrid\Column\Options\Appearance;
use WellCommerce\Component\DataGrid\Column\Options\Filter;
use WellCommerce\Component\DataGrid\Column\Options\Sorting;

/**
 * Class DictionaryDataGrid
 *
 * @author  Adam Piotrowski <adam@wellcommerce.org>
 */
class DictionaryDataGrid extends AbstractDataGrid
{
    public function configureColumns(ColumnCollection $collection)
    {
        $collection->add(new Column([
            'id'         => 'id',
            'caption'    => 'dictionary.label.id',
            'sorting'    => new Sorting([
                'default_order' => ColumnInterface::SORT_DIR_DESC,
            ]),
            'appearance' => new Appearance([
                'width'   => 90,
                'visible' => false,
            ]),
            'filter'     => new Filter([
                'type' => Filter::FILTER_BETWEEN,
            ]),
        ]));

        $collection->add(new Column([
            'id'      => 'identifier',
            'caption' => 'dictionary.label.identifier',
        ]));

        $collection->add(new Column([
            'id'      => 'translation',
            'caption' => 'dictionary.label.translation',
        ]));

        $collection->add(new Column([
            'id'      => 'locale',
            'caption' => 'dictionary.label.locale',
        ]));
    }
    
    public function getIdentifier(): string
    {
        return 'dictionary';
    }
}
