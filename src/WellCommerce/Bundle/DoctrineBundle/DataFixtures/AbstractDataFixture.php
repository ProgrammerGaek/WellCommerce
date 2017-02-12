<?php
/**
 * WellCommerce Open-Source E-Commerce Platform
 *
 * This file is part of the WellCommerce package.
 *
 * (c) Adam Piotrowski <adam@wellcommerce.org>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace WellCommerce\Bundle\DoctrineBundle\DataFixtures;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use WellCommerce\Bundle\LayoutBundle\Entity\LayoutBox;
use WellCommerce\Bundle\LayoutBundle\Entity\LayoutBoxInterface;
use WellCommerce\Bundle\LayoutBundle\Entity\LayoutBoxTranslation;
use WellCommerce\Bundle\LocaleBundle\Entity\LocaleInterface;

/**
 * Class AbstractDataFixture
 *
 * @author  Adam Piotrowski <adam@wellcommerce.org>
 */
abstract class AbstractDataFixture extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    const FALLBACK_HIERARCHY = 999;
    
    /**
     * @var array
     */
    protected $hierarchy;
    
    /**
     * @var ContainerInterface
     */
    protected $container;
    
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    abstract public function load(ObjectManager $manager);
    
    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function getOrder()
    {
        $hierarchy = $this->container->getParameter('fixtures_load_order');
        $className = get_class($this);
        if (isset($hierarchy[$className]) && (int)$hierarchy[$className] >= 0) {
            return (int)$hierarchy[$className];
        }
        
        return self::FALLBACK_HIERARCHY;
    }
    
    /**
     * @return bool
     */
    protected function isEnabled()
    {
        $enabledFixtures = $this->container->getParameter('enabled_fixtures');
        $className       = get_class($this);
        
        if (array_key_exists($className, $enabledFixtures)) {
            return (bool)$enabledFixtures[$className];
        }
        
        return false;
    }
    
    /**
     * @return \Faker\Generator
     */
    protected function getFakerGenerator()
    {
        return FakerFactory::create();
    }
    
    /**
     * Returns random entity or collection of entities
     *
     * @param       $referencePrefix
     * @param array $samples
     * @param int   $limit
     *
     * @return object
     */
    protected function randomizeSamples($referencePrefix, array $samples, $limit = 1)
    {
        $sample = array_rand($samples, $limit);
        
        if (1 === $limit) {
            $referenceName = sprintf('%s_%s', $referencePrefix, $samples[$sample]);
            
            return $this->getReference($referenceName);
        } else {
            $collection = new ArrayCollection();
            foreach ($sample as $index) {
                $referenceName = sprintf('%s_%s', $referencePrefix, $samples[$index]);
                $collection->add($this->getReference($referenceName));
            }
            
            return $collection;
        }
    }
    
    protected function get($name)
    {
        return $this->container->get($name);
    }
    
    protected function getDefaultLocale(): string
    {
        return $this->container->getParameter('locale');
    }
    
    /**
     * @return array|LocaleInterface[]
     */
    protected function getLocales(): array
    {
        return $this->container->get('locale.repository')->findAll();
    }
    
    protected function importAdminMenuConfiguration($file)
    {
        $reflection = new \ReflectionClass($this);
        $directory  = dirname($reflection->getFileName());
        $locator    = new FileLocator($directory . '/../../Resources/config/admin_menu');
        $importer   = $this->container->get('admin_menu.importer.xml');
        
        $importer->import($file, $locator);
    }
    
    protected function createLayoutBoxes(ObjectManager $manager, array $boxes)
    {
        foreach ($boxes as $identifier => $params) {
            $layoutBox = $this->createLayoutBox($identifier, $params);
            $manager->persist($layoutBox);
        }
    }
    
    private function createLayoutBox(string $identifier, array $params = []): LayoutBoxInterface
    {
        $layoutBox = new LayoutBox();
        $layoutBox->setIdentifier($identifier);
        $layoutBox->setBoxType($params['type']);
        $layoutBox->setSettings($params['settings'] ?? []);
        foreach ($this->getLocales() as $locale) {
            /** @var LayoutBoxTranslation $translation */
            $translation = $layoutBox->translate($locale->getCode());
            $translation->setName($params['name']);
        }
        
        $layoutBox->mergeNewTranslations();
        
        return $layoutBox;
    }
}
