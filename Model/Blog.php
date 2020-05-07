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

namespace Mageplaza\BlogSampleData\Model;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Driver\File as DriverFile;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Setup\SampleData\Context as SampleDataContext;
use Magento\Framework\Setup\SampleData\FixtureManager;
use Magento\MediaStorage\Model\File\Uploader;
use Mageplaza\Blog\Model\AuthorFactory;
use Mageplaza\Blog\Model\CategoryFactory;
use Mageplaza\Blog\Model\CommentFactory;
use Mageplaza\Blog\Model\PostFactory;
use Mageplaza\Blog\Model\PostHistoryFactory;
use Mageplaza\Blog\Model\PostLikeFactory;
use Mageplaza\Blog\Model\TagFactory;
use Mageplaza\Blog\Model\TopicFactory;

/**
 * Class Blog
 * @package Mageplaza\BlogSampleData\Model
 */
class Blog
{
    /**
     * @var FixtureManager
     */
    protected $fixtureManager;

    /**
     * @var Csv
     */
    protected $csvReader;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var StockItemInterfaceFactory
     */
    protected $stockItemInterfaceFactory;

    /**
     * @var WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var array
     */
    protected $categoryIdMapFields = [];
    /**
     * @var array
     */
    protected $authorIdMapFields = [];
    /**
     * @var array
     */
    protected $postIdMapFields = [];
    /**
     * @var array
     */
    protected $topicIdMapFields = [];
    /**
     * @var array
     */
    protected $tagIdMapFields = [];
    /**
     * @var array
     */
    protected $commentIdMapFields = [];

    /**
     * @var Reader
     */
    protected $moduleReader;

    /**
     * @var string
     */
    protected $viewDir = '';

    /**
     * @var File
     */
    protected $ioFile;
    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;
    /**
     * @var DirectoryList
     */
    protected $directoryList;
    /**
     * @var AuthorFactory
     */
    protected $authorFactory;
    /**
     * @var PostFactory
     */
    protected $postFactory;
    /**
     * @var Filesystem
     */
    protected $filesystem;
    /**
     * @var DriverFile
     */
    protected $driverFile;
    /**
     * @var TopicFactory
     */
    protected $topicFactory;
    /**
     * @var TagFactory
     */
    protected $tagFactory;
    /**
     * @var int
     */
    protected $sampleProductId;
    /**
     * @var PostHistoryFactory
     */
    protected $postHistoryFactory;
    /**
     * @var CommentFactory
     */
    protected $commentFactory;
    /**
     * @var PostLikeFactory
     */
    protected $postLikeFactory;
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * Blog constructor.
     * @param SampleDataContext $sampleDataContext
     * @param ProductRepositoryInterface $productRepository
     * @param ProductFactory $productFactory
     * @param StockItemInterfaceFactory $stockItemInterfaceFactory
     * @param Reader $moduleReader
     * @param File $ioFile
     * @param Filesystem $filesystem
     * @param DriverFile $driverFile
     * @param DirectoryList $directoryList
     * @param CategoryFactory $categoryFactory
     * @param AuthorFactory $authorFactory
     * @param PostFactory $postFactory
     * @param TopicFactory $topicFactory
     * @param TagFactory $tagFactory
     * @param PostHistoryFactory $postHistoryFactory
     * @param ResourceConnection $resourceConnection
     * @param CommentFactory $commentFactory
     * @param PostLikeFactory $postLikeFactory
     * @throws FileSystemException
     */
    public function __construct(
        SampleDataContext $sampleDataContext,
        ProductRepositoryInterface $productRepository,
        ProductFactory $productFactory,
        StockItemInterfaceFactory $stockItemInterfaceFactory,
        Reader $moduleReader,
        File $ioFile,
        Filesystem $filesystem,
        DriverFile $driverFile,
        DirectoryList $directoryList,
        CategoryFactory $categoryFactory,
        AuthorFactory $authorFactory,
        PostFactory $postFactory,
        TopicFactory $topicFactory,
        TagFactory $tagFactory,
        PostHistoryFactory $postHistoryFactory,
        ResourceConnection $resourceConnection,
        CommentFactory $commentFactory,
        PostLikeFactory $postLikeFactory
    ) {
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
        $this->stockItemInterfaceFactory = $stockItemInterfaceFactory;
        $this->moduleReader = $moduleReader;
        $this->ioFile = $ioFile;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->categoryFactory = $categoryFactory;
        $this->directoryList = $directoryList;
        $this->authorFactory = $authorFactory;
        $this->postFactory = $postFactory;
        $this->driverFile = $driverFile;
        $this->topicFactory = $topicFactory;
        $this->tagFactory = $tagFactory;
        $this->postHistoryFactory = $postHistoryFactory;
        $this->commentFactory = $commentFactory;
        $this->postLikeFactory = $postLikeFactory;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param array $fixtures
     *
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws LocalizedException
     * @throws StateException
     * @throws Exception
     */
    public function install(array $fixtures)
    {
        $this->copyWysiwygImage();
        $rootCatId = $this->categoryFactory->create()->load('root', 'url_key')->getId();
        $product = $this->createNewSampleProduct();
        $this->sampleProductId = $product->getId();
        $connection = $this->resourceConnection->getConnection();
        foreach ($fixtures as $fileName) {
            $file = $this->fixtureManager->getFixture($fileName);
            if (!$this->ioFile->fileExists($file)) {
                continue;
            }

            $rows = $this->csvReader->getData($file);
            $header = array_shift($rows);
            foreach ($rows as $row) {
                $data = [];
                foreach ($row as $key => $value) {
                    $data[$header[$key]] = $value;
                }

                switch ($fileName) {
                    case 'Mageplaza_BlogSampleData::fixtures/mageplaza_blog_category.csv':
                        if ($data['url_key'] === 'root') {
                            $this->categoryIdMapFields[$data['category_id']] = $rootCatId;
                            continue 2;
                        }
                        $oldCategoryId = $data['category_id'];
                        $data = $this->processCategoryData($data);
                        $category = $this->categoryFactory->create()
                            ->addData($data)
                            ->save();

                        $this->categoryIdMapFields[$oldCategoryId] = $category->getId();
                        break;
                    case 'Mageplaza_BlogSampleData::fixtures/mageplaza_blog_author.csv':
                        $oldAuthorId = $data['user_id'];
                        $data = $this->processAuthorData($data);
                        $author = $this->authorFactory->create()
                            ->addData($data)
                            ->save();
                        $this->authorIdMapFields[$oldAuthorId] = $author->getId();
                        break;
                    case 'Mageplaza_BlogSampleData::fixtures/mageplaza_blog_post.csv':
                        $oldPostId = $data['post_id'];
                        $data = $this->processPostData($data);
                        $post = $this->postFactory->create()
                            ->addData($data)
                            ->save();
                        $this->postIdMapFields[$oldPostId] = $post->getId();
                        break;
                    case 'Mageplaza_BlogSampleData::fixtures/mageplaza_blog_topic.csv':
                        $oldTopicId = $data['topic_id'];
                        $data = $this->processTopicData($data);
                        $topic = $this->topicFactory->create()
                            ->addData($data)
                            ->save();
                        $this->topicIdMapFields[$oldTopicId] = $topic->getId();
                        break;
                    case 'Mageplaza_BlogSampleData::fixtures/mageplaza_blog_tag.csv':
                        $oldTagId = $data['tag_id'];
                        $data = $this->processTagData($data);
                        $tag = $this->tagFactory->create()
                            ->addData($data)
                            ->save();
                        $this->tagIdMapFields[$oldTagId] = $tag->getId();
                        break;
                    case 'Mageplaza_BlogSampleData::fixtures/mageplaza_blog_post_history.csv':
                        $data = $this->processPostHistoryData($data);
                        $this->postHistoryFactory->create()
                            ->addData($data)
                            ->save();

                        break;
                    case 'Mageplaza_BlogSampleData::fixtures/mageplaza_blog_post_traffic.csv':
                        $table = $this->resourceConnection->getTableName('mageplaza_blog_post_traffic');
                        unset($data['traffic_id']);
                        $data['post_id'] = $this->postIdMapFields[$data['post_id']];
                        $connection->insert($table, $data);
                        break;
                    case 'Mageplaza_BlogSampleData::fixtures/mageplaza_blog_post_tag.csv':
                        $table = $this->resourceConnection->getTableName('mageplaza_blog_post_tag');
                        $data['post_id'] = $this->postIdMapFields[$data['post_id']];
                        $data['tag_id'] = $this->tagIdMapFields[$data['tag_id']];
                        $connection->insert($table, $data);
                        break;
                    case 'Mageplaza_BlogSampleData::fixtures/mageplaza_blog_post_topic.csv':
                        $table = $this->resourceConnection->getTableName('mageplaza_blog_post_topic');
                        $data['post_id'] = $this->postIdMapFields[$data['post_id']];
                        $data['topic_id'] = $this->topicIdMapFields[$data['topic_id']];
                        $connection->insert($table, $data);
                        break;
                    case 'Mageplaza_BlogSampleData::fixtures/mageplaza_blog_post_category.csv':
                        $table = $this->resourceConnection->getTableName('mageplaza_blog_post_category');
                        $data['post_id'] = $this->postIdMapFields[$data['post_id']];
                        $data['category_id'] = $this->categoryIdMapFields[$data['category_id']];
                        $connection->insert($table, $data);
                        break;
                    case 'Mageplaza_BlogSampleData::fixtures/mageplaza_blog_comment.csv':
                        $oldCommentId = $data['comment_id'];
                        $data = $this->processCommentData($data);
                        $comment = $this->commentFactory->create()->addData($data)->save();
                        $this->commentIdMapFields[$oldCommentId] = $comment->getId();
                        break;
                    case 'Mageplaza_BlogSampleData::fixtures/mageplaza_blog_post_like.csv':
                        $data = $this->processPostLikeData($data);
                        $this->postLikeFactory->create()->addData($data)->save();
                        break;
                    default:
                }
            }
        }
    }

    /**
     * @throws FileSystemException
     * @throws Exception
     */
    protected function copyWysiwygImage()
    {
        $wysiwygDirectory = $this->getFilePath('/files/wysiwyg/');
        $destinationDirectory = $this->mediaDirectory->getAbsolutePath('wysiwyg/');
        $this->ioFile->checkAndCreateFolder($destinationDirectory);
        $files = $this->driverFile->readDirectoryRecursively($wysiwygDirectory);
        foreach ($files as $file) {
            $destinationFile = str_replace($wysiwygDirectory, $destinationDirectory, $file);
            $pathInfo = $this->ioFile->getPathInfo($destinationFile);
            $this->ioFile->checkAndCreateFolder($pathInfo['dirname']);

            $this->ioFile->cp($file, $destinationFile);
        }
    }

    /**
     * @param array $data
     * @return array
     */
    protected function processCategoryData($data): array
    {
        unset(
            $data['category_id'],
            $data['url_key'],
            $data['position'],
            $data['level'],
            $data['children_count'],
            $data['created_at'],
            $data['updated_at']
        );

        $data['parent_id'] = $this->categoryIdMapFields[$data['parent_id']];

        $parentCategory = $this->categoryFactory->create()->load($data['parent_id']);
        $data['path'] = $parentCategory->getPath();

        return $data;
    }

    /**
     * @param array $data
     * @return array
     * @throws Exception
     */
    protected function processAuthorData($data): array
    {
        unset(
            $data['user_id'],
            $data['customer_id'],
            $data['created_at'],
            $data['updated_at']
        );

        $this->copyImage($data['image'], '/files/author/image/', 'mageplaza/blog/auth/');

        return $data;
    }

    /**
     * @param array $data
     * @return array
     * @throws Exception
     */
    protected function processPostData(array $data): array
    {
        unset(
            $data['post_id']
        );
        $data['store_ids'] = '0';
        $data['author_id'] = $this->authorIdMapFields[$data['author_id']];
        $data['modifier_id'] = $this->authorIdMapFields[$data['modifier_id']];
        $data['products_data'] = [$this->sampleProductId => ['position' => '0']];
        $this->copyImage($data['image'], '/files/post/image/', 'mageplaza/blog/post/');

        return $data;
    }

    /**
     * @param $path
     * @return string
     */
    protected function getFilePath($path)
    {
        if (!$this->viewDir) {
            $this->viewDir = $this->moduleReader->getModuleDir(
                Dir::MODULE_VIEW_DIR,
                'Mageplaza_BlogSampleData'
            );
        }

        return $this->viewDir . $path;
    }

    /**
     * @throws FileSystemException
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws StateException
     */
    protected function createNewSampleProduct()
    {
        // check product is exists
        try {
            $product = $this->productRepository->get('mageplaza_blog_sample_product');
        } catch (NoSuchEntityException $e) {
            $product = null;
        }

        // create new sample product if not exits
        if (!$product || !$product->getId()) {
            /** @var Product $product */
            $product = $this->productFactory->create();

        }

        $product->setTypeId('simple')
            ->setAttributeSetId(4)
            ->setName('Mageplaza Blog Sample Product')
            ->setSku('mageplaza_blog_sample_product')
            ->setDescription('Description for product')
            ->setPrice(0)
            ->setQty(100)
            ->setVisibility(Visibility::VISIBILITY_BOTH)
            ->setStatus(Status::STATUS_ENABLED);
        $product = $this->setProductImage($product, 'https://picsum.photos/400');

        /** @var StockItemInterface $stockItem */
        $stockItem = $this->stockItemInterfaceFactory->create();
        $stockItem->setQty(100)
            ->setIsInStock(true);
        $extensionAttributes = $product->getExtensionAttributes();
        $extensionAttributes->setStockItem($stockItem);

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->productRepository;
        $product = $productRepository->save($product);

        return $product;
    }

    /**
     * @param Product $product
     * @param $imageUrl
     * @param bool $visible
     * @param array $imageType
     * @return bool|string
     * @throws FileSystemException
     * @throws Exception
     */
    public function setProductImage(
        $product,
        $imageUrl,
        $visible = false,
        $imageType = ['image', 'small_image', 'thumbnail']
    ) {
        /** @var string $tmpDir */
        $tmpDir = $this->getMediaDirTmpDir();
        /** create folder if it is not exists */
        $this->ioFile->checkAndCreateFolder($tmpDir);
        $pathInfo = $this->ioFile->getPathInfo($imageUrl);
        $fileName = $pathInfo['basename'] . '.jpg';
        /** @var string $newFileName */
        $newFileName = $tmpDir . $fileName;
        /** read file from URL and copy it to the new destination */
        $result = $this->ioFile->read($imageUrl, $newFileName);
        if ($result) {
            /** add saved file to the $product gallery */
            $product->addImageToMediaGallery($newFileName, $imageType, true, $visible);
        }
        return $product;
    }

    /**
     * @return string
     * @throws FileSystemException
     */
    protected function getMediaDirTmpDir()
    {
        return $this->directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'tmp';
    }

    /**
     * @param string $filePath
     * @param string $folderPath
     * @param string $destinationFolderPath
     * @return string
     * @throws Exception
     */
    protected function copyImage($filePath, $folderPath, $destinationFolderPath)
    {
        if (!$filePath) {
            return '';
        }
        $filePath = ltrim($filePath, '/');
        $pathInfo = $this->ioFile->getPathInfo($filePath);
        $fileName = $pathInfo['basename'];
        $dispersion = $pathInfo['dirname'];
        $file = $this->getFilePath($folderPath . $filePath);
        $this->ioFile->checkAndCreateFolder('pub/media/' . $destinationFolderPath . $dispersion);
        $fileName = Uploader::getCorrectFileName($fileName);
        $fileName = Uploader::getNewFileName(
            $this->mediaDirectory->getAbsolutePath($destinationFolderPath . $dispersion . '/' . $fileName)
        );
        $destinationFile = $this->mediaDirectory->getAbsolutePath(
            $destinationFolderPath . $dispersion . '/' . $fileName
        );

        $destinationFilePath = $this->mediaDirectory->getAbsolutePath($destinationFile);
        $this->ioFile->cp($file, $destinationFilePath);

        return $fileName;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function processTopicData(array $data): array
    {
        unset(
            $data['topic_id'],
            $data['url_key']
        );
        $data['store_ids'] = '0';

        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function processTagData(array $data): array
    {
        unset(
            $data['tag_id'],
            $data['url_key']
        );
        $data['store_ids'] = '0';

        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function processPostHistoryData(array $data): array
    {
        unset(
            $data['history_id'],
            $data['product_ids']
        );
        $data['store_ids'] = '0';

        $data['author_id'] = $this->authorIdMapFields[$data['author_id']];
        $data['modifier_id'] = $this->authorIdMapFields[$data['modifier_id']];
        $data['post_id'] = $this->postIdMapFields[$data['post_id']];
        $data['products_data'] = [$this->sampleProductId => ['position' => '0']];

        $categoryIds = explode(',', $data['category_ids']);
        $data['categories_ids'] = array_filter($categoryIds, function ($categoryId) {
            return $this->categoryIdMapFields[$categoryId];
        });
        $tagIds = explode(',', $data['tag_ids']);
        $data['tags_ids'] = array_filter($tagIds, function ($tagId) {
            return $this->tagIdMapFields[$tagId];
        });
        $topicIds = explode(',', $data['topic_ids']);
        $data['topics_ids'] = array_filter($topicIds, function ($topicId) {
            return $this->topicIdMapFields[$topicId];
        });

        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function processCommentData(array $data): array
    {
        unset(
            $data['comment_id']
        );
        $data['post_id'] = $this->postIdMapFields[$data['post_id']];
        $data['entity_id'] = '0';
        $data['store_ids'] = '0';
        $data['user_email'] = 'test@mageplaza.com';
        $data['reply_id'] = $data['reply_id'] ? $this->commentIdMapFields[$data['reply_id']] : $data['reply_id'];

        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function processPostLikeData(array $data): array
    {
        unset(
            $data['like_id']
        );
        $data['post_id'] = $this->postIdMapFields[$data['post_id']];
        $data['entity_id'] = '0';

        return $data;
    }
}
