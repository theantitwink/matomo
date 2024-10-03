<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Translation\Loader;

use Piwik\Translation\Loader\DevelopmentLoader;

/**
 * @group Translation
 */
class DevelopmentLoaderTest extends \PHPUnit\Framework\TestCase
{
    private $translations = array(
        'General' => array(
            'translationId' => 'Hello',
        ),
    );

    public function testShouldReturnTranslationIdsIfDevelopmentLanguage()
    {
        $wrappedLoader = $this->getMockForAbstractClass('Piwik\Translation\Loader\LoaderInterface');
        $loader = new DevelopmentLoader($wrappedLoader);

        $wrappedLoader->expects($this->once())
            ->method('load')
            ->with('en', array('directory'))
            ->willReturn($this->translations);

        $translations = $loader->load(DevelopmentLoader::LANGUAGE_ID, array('directory'));

        $expected = array(
            'General' => array(
                'translationId' => 'General_translationId',
            ),
        );

        $this->assertEquals($expected, $translations);
    }

    public function testShouldUseDecoratedLoaderIfNotDevelopmentLanguage()
    {
        $wrappedLoader = $this->getMockForAbstractClass('Piwik\Translation\Loader\LoaderInterface');
        $loader = new DevelopmentLoader($wrappedLoader);

        $wrappedLoader->expects($this->once())
            ->method('load')
            ->with('fr', array('directory'))
            ->willReturn($this->translations);

        $translations = $loader->load('fr', array('directory'));

        $this->assertEquals($this->translations, $translations);
    }
}
