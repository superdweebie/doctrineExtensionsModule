<?php

namespace Sds\DoctrineExtensionsModule\Test\Controller;

use Sds\DoctrineExtensionsModule\Test\TestAsset\TestData;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\Http\Header\Accept;
use Zend\Http\Header\ContentType;

class JsonRestfulControllerPatchTest extends AbstractHttpControllerTestCase{

    protected static $staticDcumentManager;

    protected static $dbDataCreated = false;

    public static function tearDownAfterClass(){
        //Cleanup db after all tests have run
        TestData::remove(static::$staticDcumentManager);
    }

    public function setUp(){

        $this->setApplicationConfig(
            include __DIR__ . '/../../../../test.application.config.php'
        );

        parent::setUp();

        $this->documentManager = $this->getApplicationServiceLocator()->get('doctrine.odm.documentmanager.default');
        static::$staticDcumentManager = $this->documentManager;

        if ( ! static::$dbDataCreated){
            //Create data in the db to query against
            TestData::create($this->documentManager);
            static::$dbDataCreated = true;
        }
    }

    public function testCreateViaPatch(){

        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod('PATCH')
            ->setContent('{"type": "card"}')
            ->getHeaders()->addHeaders([$accept, ContentType::fromString('Content-type: application/json')]);

        $this->dispatch('/rest/game/uno');

        $response = $this->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertResponseStatusCode(204);
        $this->assertFalse(isset($result));

        $game = $this->documentManager->getRepository('Sds\DoctrineExtensionsModule\Test\TestAsset\Document\Game')->find('uno');
        $this->assertEquals('card', $game->getType());
    }

    public function testPatchExistingDocument(){

        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod('PATCH')
            ->setContent('{"type": "kids"}')
            ->getHeaders()->addHeaders([$accept, ContentType::fromString('Content-type: application/json')]);

        $this->dispatch('/rest/game/feed-the-kitty');

        $response = $this->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertResponseStatusCode(204);
        $this->assertFalse(isset($result));

        $game = $this->documentManager->getRepository('Sds\DoctrineExtensionsModule\Test\TestAsset\Document\Game')->find('feed-the-kitty');
        $this->assertEquals('kids', $game->getType());
        $this->assertEquals('gamewright', $game->getPublisher()->getName());
    }

    public function testPatchValidationFail(){

        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod('PATCH')
            ->setContent('{"nickname": "!!not valid!!"}')
            ->getHeaders()->addHeaders([$accept, ContentType::fromString('Content-type: application/json')]);

        $this->dispatch('/rest/author/harry');

        $result = json_decode($this->getResponse()->getContent(), true);
        $this->assertResponseStatusCode(500);
        $this->assertEquals('Content-Type: application/api-problem+json', $this->getResponse()->getHeaders()->get('Content-Type')->toString());

        $this->assertEquals('/exception/document-validation-failed', $result['describedBy']);
        $this->assertEquals('Document validation failed', $result['title']);
        $this->assertTrue(isset($result['validatorMessages']['nickname']));

        $author = $this->documentManager->getRepository('Sds\DoctrineExtensionsModule\Test\TestAsset\Document\Author')->find('harry');
        $this->assertEquals('harry', $author->getName());
    }

    public function testPatchDeep404(){

        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod('PATCH')
            ->setContent('{"country": {"$ref": "country/us"}}')
            ->getHeaders()->addHeaders([$accept, ContentType::fromString('Content-type: application/json')]);

        $this->dispatch('/rest/game/does-not-exist/author');

        $result = json_decode($this->getResponse()->getContent(), true);
        $this->assertResponseStatusCode(404);
        $this->assertEquals('Content-Type: application/api-problem+json', $this->getResponse()->getHeaders()->get('Content-Type')->toString());
        $this->assertEquals('/exception/document-not-found', $result['describedBy']);
        $this->assertEquals('Document not found', $result['title']);
    }

    public function testPatchEmbedded404(){

        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod('PATCH')
            ->setContent('{"country": {"$ref": "country/us"}}')
            ->getHeaders()->addHeaders([$accept, ContentType::fromString('Content-type: application/json')]);

        $this->dispatch('/rest/game/feed-the-kitty/does-not-exist');

        $result = json_decode($this->getResponse()->getContent(), true);
        $this->assertResponseStatusCode(404);
        $this->assertEquals('Content-Type: application/api-problem+json', $this->getResponse()->getHeaders()->get('Content-Type')->toString());
        $this->assertEquals('/exception/document-not-found', $result['describedBy']);
        $this->assertEquals('Document not found', $result['title']);
    }

    public function testPatchEmbeddedOne(){

        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod('PATCH')
            ->setContent('{"country": {"$ref": "country/us"}}')
            ->getHeaders()->addHeaders([$accept, ContentType::fromString('Content-type: application/json')]);

        $this->dispatch('/rest/game/feed-the-kitty/publisher');

        $response = $this->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertResponseStatusCode(204);
        $this->assertFalse(isset($result));

        $game = $this->documentManager->getRepository('Sds\DoctrineExtensionsModule\Test\TestAsset\Document\Game')->find('feed-the-kitty');
        $publisher = $game->getPublisher();
        $this->assertEquals('gamewright', $publisher->getName());
        $this->assertEquals('us', $publisher->getCountry()->getName());
        $this->assertEquals('Little Rock', $publisher->getCity());
    }

    public function testPatchEmbeddedListItem(){

        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod('PATCH')
            ->setContent('{"type": "custom"}')
            ->getHeaders()->addHeaders([$accept, ContentType::fromString('Content-type: application/json')]);

        $this->dispatch('/rest/game/feed-the-kitty/components/action-dice');

        $response = $this->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertResponseStatusCode(204);
        $this->assertFalse(isset($result));

        $game = $this->documentManager->getRepository('Sds\DoctrineExtensionsModule\Test\TestAsset\Document\Game')->find('feed-the-kitty');
        foreach ($game->getComponents() as $component){
            if ($component->getName() == 'action-dice'){
                break;
            }
        }
        $this->assertEquals('custom', $component->getType());
        $this->assertCount(1, $component->getManufacturers());
    }

    public function testPatchEmbeddedList(){

        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod('PATCH')
            ->setContent('[
                    {"name": "instructions", "type": "paper"},
                    {"name": "game-box", "type": "telescoping"}
                ]')
            ->getHeaders()->addHeaders([$accept, ContentType::fromString('Content-type: application/json')]);

        $this->dispatch('/rest/game/feed-the-kitty/components');

        $response = $this->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertResponseStatusCode(204);
        $this->assertFalse(isset($result));

        $game = $this->documentManager->getRepository('Sds\DoctrineExtensionsModule\Test\TestAsset\Document\Game')->find('feed-the-kitty');
        $components = $game->getComponents();
        $types = array_map(function($component){return $component->getType();}, $components->toArray());
        $this->assertContains('paper', $types);
        $this->assertContains('telescoping', $types);
        $this->assertTrue(2 < count($components));
    }

    public function testUpdateEmbeddedListItemWithNew(){

        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod('PATCH')
            ->setContent('{"type": "paper"}')
            ->getHeaders()->addHeaders([$accept, ContentType::fromString('Content-type: application/json')]);

        $this->dispatch('/rest/game/feed-the-kitty/components/feeback-form');

        $response = $this->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertResponseStatusCode(204);
        $this->assertFalse(isset($result));

        $game = $this->documentManager->getRepository('Sds\DoctrineExtensionsModule\Test\TestAsset\Document\Game')->find('feed-the-kitty');
        foreach($game->getComponents() as $component){
            if ($component->getName() == 'feedback-form'){
                break;
            }
        }
        $this->assertEquals('paper', $component->getType());
    }

    public function testPatchReferencedOne(){

        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod('PATCH')
            ->setContent('{"nickname": "jamie"}')
            ->getHeaders()->addHeaders([$accept, ContentType::fromString('Content-type: application/json')]);

        $this->dispatch('/rest/game/feed-the-kitty/author');

        $response = $this->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertResponseStatusCode(204);
        $this->assertFalse(isset($result));

        $game = $this->documentManager->getRepository('Sds\DoctrineExtensionsModule\Test\TestAsset\Document\Game')->find('feed-the-kitty');
        $author = $game->getAuthor();
        $this->assertEquals('james', $author->getName());
        $this->assertEquals('jamie', $author->getNickname());
        $this->assertEquals('germany', $author->getCountry()->getName());
    }

    public function testPatchReferencedOneWithReference(){

        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod('PATCH')
            ->setContent('{"$ref": "author/bill"}')
            ->getHeaders()->addHeaders([$accept, ContentType::fromString('Content-type: application/json')]);

        $this->dispatch('/rest/game/feed-the-kitty/author');

        $response = $this->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertResponseStatusCode(204);
        $this->assertFalse(isset($result));

        $game = $this->documentManager->getRepository('Sds\DoctrineExtensionsModule\Test\TestAsset\Document\Game')->find('feed-the-kitty');
        $author = $game->getAuthor();
        $this->assertEquals('bill', $author->getName());
        $this->assertEquals('germany', $author->getCountry()->getName());
    }

    public function testPatchReferencedOneWithNew(){

        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod('PATCH')
            ->setContent('{"name": "oscar"}')
            ->getHeaders()->addHeaders([$accept, ContentType::fromString('Content-type: application/json')]);

        $this->dispatch('/rest/game/feed-the-kitty/author');

        $response = $this->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertResponseStatusCode(204);
        $this->assertFalse(isset($result));

        $game = $this->documentManager->getRepository('Sds\DoctrineExtensionsModule\Test\TestAsset\Document\Game')->find('feed-the-kitty');
        $author = $game->getAuthor();
        $this->assertEquals('oscar', $author->getName());

        $author = $this->documentManager->getRepository('Sds\DoctrineExtensionsModule\Test\TestAsset\Document\Author')->find('oscar');
        $this->assertTrue(isset($author));
    }

    public function testPatchReferencedListItem(){

        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod('PATCH')
            ->setContent('{"content": "this is the review content"}')
            ->getHeaders()->addHeaders([$accept, ContentType::fromString('Content-type: application/json')]);

        $this->dispatch('/rest/game/feed-the-kitty/reviews/great-review');

        $response = $this->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertResponseStatusCode(204);
        $this->assertFalse(isset($result));

        $this->documentManager->clear();
        $game = $this->documentManager->getRepository('Sds\DoctrineExtensionsModule\Test\TestAsset\Document\Game')->find('feed-the-kitty');
        foreach ($game->getReviews() as $review){
            if ($review->getTitle() == 'great-review'){
                break;
            }
        }
        $this->assertEquals('great-review', $review->getTitle());
        $this->assertEquals('harry', $review->getAuthor()->getName());
        $this->assertEquals('this is the review content', $review->getContent());
    }

    public function testPatchReferencedListItemWithNew(){

        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod('PATCH')
            ->setContent('{"content" : "more review content"}')
            ->getHeaders()->addHeaders([$accept, ContentType::fromString('Content-type: application/json')]);

        $this->dispatch('/rest/game/feed-the-kitty/reviews/another-review');

        $response = $this->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertResponseStatusCode(204);
        $this->assertFalse(isset($result));

        $game = $this->documentManager->getRepository('Sds\DoctrineExtensionsModule\Test\TestAsset\Document\Game')->find('feed-the-kitty');
        $review = $game->getReviews()[2];
        $this->assertEquals('another-review', $review->getTitle());
        $this->assertEquals('more review content', $review->getContent());
    }

    public function testPatchReferencedList(){

        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod('PATCH')
            ->setContent('[
                    {"title" : "new-review-1"},
                    {"title" : "new-review-2"}
                ]')
            ->getHeaders()->addHeaders([$accept, ContentType::fromString('Content-type: application/json')]);

        $this->dispatch('/rest/game/feed-the-kitty/reviews');

        $response = $this->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertResponseStatusCode(204);
        $this->assertFalse(isset($result));

        $game = $this->documentManager->getRepository('Sds\DoctrineExtensionsModule\Test\TestAsset\Document\Game')->find('feed-the-kitty');
        $this->assertTrue(2 < count($game->getReviews()));
    }


    public function testPatchList(){

        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod('PATCH')
            ->setContent('[
                    {"name": "feed-the-kitty", "type": "animal"},
                    {"name": "exploding-chicken", "type": "dice"},
                    {"name": "kings-at-arms", "type": "card"}
                ]')
            ->getHeaders()->addHeaders([$accept, ContentType::fromString('Content-type: application/json')]);

        $this->dispatch('/rest/game');

        $response = $this->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertResponseStatusCode(204);
        $this->assertFalse(isset($result));

        $repository = $this->documentManager->getRepository('Sds\DoctrineExtensionsModule\Test\TestAsset\Document\Game');
        $game = $repository->find('exploding-chicken');
        $this->assertTrue(isset($game));
        $game = $repository->find('kings-at-arms');
        $this->assertTrue(isset($game));
        $game = $repository->find('feed-the-kitty');
        $this->assertTrue(isset($game));
        $this->assertEquals('animal', $game->getType());
    }

    public function testPatchExistingDocumentId(){

        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod('PATCH')
            ->setContent('{"name": "thomas-dean"}')
            ->getHeaders()->addHeaders([$accept, ContentType::fromString('Content-type: application/json')]);

        $this->dispatch('/rest/author/thomas');

        $response = $this->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertResponseStatusCode(204);
        $this->assertFalse(isset($result));

        $this->assertEquals('Location: /rest/author/thomas-dean', $response->getHeaders()->get('Location')->toString());

        $this->documentManager->clear();
        $author = $this->documentManager->getRepository('Sds\DoctrineExtensionsModule\Test\TestAsset\Document\Author')->find('thomas');
        $this->assertFalse(isset($author));
        $author = $this->documentManager->getRepository('Sds\DoctrineExtensionsModule\Test\TestAsset\Document\Author')->find('thomas-dean');
        $this->assertTrue(isset($author));
        $this->assertEquals('tommy', $author->getNickname());

        $review = $this->documentManager->getRepository('Sds\DoctrineExtensionsModule\Test\TestAsset\Document\Review')->find('bad-review');
        $this->assertEquals('thomas-dean', $review->getAuthor()->getName());
    }
}
