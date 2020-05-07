<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_BlogSampleData
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\BlogSampleData\Setup;

use Exception;
use Magento\Framework\Setup;
use Mageplaza\BlogSampleData\Model\Blog;

/**
 * Class Installer
 * @package Mageplaza\BlogSampleData\Setup
 */
class Installer implements Setup\SampleData\InstallerInterface
{
    /**
     * @var Blog
     */
    private $blog;

    /**
     * Installer constructor.
     *
     * @param Blog $blog
     */
    public function __construct(
        Blog $blog
    ) {
        $this->blog = $blog;
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function install()
    {
        $this->blog->install([
            'Mageplaza_BlogSampleData::fixtures/mageplaza_blog_category.csv',
            'Mageplaza_BlogSampleData::fixtures/mageplaza_blog_author.csv',
            'Mageplaza_BlogSampleData::fixtures/mageplaza_blog_post.csv',
            'Mageplaza_BlogSampleData::fixtures/mageplaza_blog_topic.csv',
            'Mageplaza_BlogSampleData::fixtures/mageplaza_blog_tag.csv',
            'Mageplaza_BlogSampleData::fixtures/mageplaza_blog_post_history.csv',
            'Mageplaza_BlogSampleData::fixtures/mageplaza_blog_post_traffic.csv',
            'Mageplaza_BlogSampleData::fixtures/mageplaza_blog_post_tag.csv',
            'Mageplaza_BlogSampleData::fixtures/mageplaza_blog_post_topic.csv',
            'Mageplaza_BlogSampleData::fixtures/mageplaza_blog_post_category.csv',
            'Mageplaza_BlogSampleData::fixtures/mageplaza_blog_comment.csv',
            'Mageplaza_BlogSampleData::fixtures/mageplaza_blog_post_like.csv',
        ]);
    }
}
