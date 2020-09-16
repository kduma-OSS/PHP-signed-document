# PHP Signed Document Library

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kduma/signed-document.svg?style=flat-square)](https://packagist.org/packages/kduma/signed-document)
[![Build Status](https://img.shields.io/travis/kduma/signed-document/master.svg?style=flat-square)](https://travis-ci.org/kduma/signed-document)
[![Quality Score](https://img.shields.io/scrutinizer/g/kduma-OSS/PHP-signed-document/.svg?style=flat-square)](https://scrutinizer-ci.com/g/kduma/signed-document)
[![Total Downloads](https://img.shields.io/packagist/dt/kduma/signed-document.svg?style=flat-square)](https://packagist.org/packages/kduma/signed-document)

PHP implementation of digital document signing. 

## Installation

You can install the package via composer:

```bash
composer require kduma/signed-document
```

## Usage

### Creating and Signing documents

```php
$signer = new \Kduma\SignedDocument\DocumentSigner(
    \ParagonIE\Halite\KeyFactory::generateSignatureKeyPair()->getSecretKey()
);

$document_1 = \Kduma\SignedDocument\Document::make("Some Content", 'test-document');
$document_1->addSignature($signer->sign($document_1, 'ceo'));

$document_2 = \Kduma\SignedDocument\Document::make("Some  Other Content", 'other-document');
$document_2->addSignature($signer->sign($document_2, 'ceo'));

$collection = new \Kduma\SignedDocument\DocumentCollection();
$collection->addDocument($document_1);
$collection->addDocument($document_2);

echo $collection->getXml();
```

Sample Output:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<documents xmlns="https://opensource.duma.sh/xml/signed-document" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="https://opensource.duma.sh/xml/signed-document https://github.com/kduma-OSS/PHP-signed-document/raw/master/schema/signed-document.xsd">
  <document>
    <content encoding="raw" sha256="950f1fb144716a62ed1f19f04d8a9ddcc35869d278641fd4f724012b3bbf7d70"><![CDATA[Some Content]]></content>
    <signature id="ceo" public-key="314004003d9fb8394a38d3c116b7f8042ca626d71594ce476a0479c0d2e51d9b7ba087845d34d44e054ae321595d6da514b71bec6ee3e0e1712d475bcddcddbfdeaa5aa59fb4b9590387f9bcd54ccffc30b48040b37cfc93548d50ce1353a19ed157d6a2">09ef8a4aca1ed3faf0886351f54fd6469b60fee91f41ad27176b77cb6a287f4850cd89661cac0f02d3cdbc8821c9defb0d337c368583241f20d208d821977d09</signature>
  </document>
  <document>
    <content encoding="raw" sha256="0f897b0422d2307fe88d8dda7b6996c4a264b468f5bc6be383b4623c2734f840"><![CDATA[Some  Other Content]]></content>
    <signature id="ceo" public-key="314004003d9fb8394a38d3c116b7f8042ca626d71594ce476a0479c0d2e51d9b7ba087845d34d44e054ae321595d6da514b71bec6ee3e0e1712d475bcddcddbfdeaa5aa59fb4b9590387f9bcd54ccffc30b48040b37cfc93548d50ce1353a19ed157d6a2">af8dcdcb2f2644edd633bf288df516c27fe25904e314cc71a9e26c7121376b10fff83615d4c6ac6df2f7512ac23efaa0cb55235c941b3c9653705741e7879808</signature>
  </document>
</documents>
```

### Verifying

```php
$signer = new \Kduma\SignedDocument\DocumentValidator();

$collection = \Kduma\SignedDocument\DocumentCollection::fromXml('<?xml version="1.0" encoding="UTF-8"?><documents xmlns="https://opensource.duma.sh/xml/signed-document"...');

var_dump($signer->validateCollection($collection));
```

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email oss@krystian.duma.sh instead of using the issue tracker.

## Credits

- [Krystian Duma](https://github.com/kduma)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
