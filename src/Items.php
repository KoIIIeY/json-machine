<?php

declare(strict_types=1);

namespace JsonMachine;

use JsonMachine\Exception\InvalidArgumentException;
use JsonMachine\JsonDecoder\ExtJsonDecoder;
use JsonMachine\JsonDecoder\ItemDecoder;

/**
 * Entry-point facade for JSON Machine.
 */
final class Items implements \IteratorAggregate, PositionAware
{
    /**
     * @var iterable
     */
    private $chunks;

    /**
     * @var string
     */
    private $jsonPointer;

    /**
     * @var ItemDecoder|null
     */
    private $jsonDecoder;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var bool
     */
    private $debugEnabled;

    private $tokensIterator;

    /**
     * @param iterable $bytesIterator
     *
     * @throws InvalidArgumentException
     */
    public function __construct($bytesIterator, array $options = [])
    {
        $options = new ItemsOptions($options);

        $this->chunks = $bytesIterator;
        $this->jsonPointer = $options['pointer'];
        $this->jsonDecoder = $options['decoder'];
        $this->debugEnabled = $options['debug'];

        if ($this->debugEnabled) {
            $tokensClass = TokensWithDebugging::class;
        } else {
            $tokensClass = Tokens::class;
        }

        $this->tokensIterator = new $tokensClass(
            $this->chunks
        );

        $this->parser = new Parser(
            $this->tokensIterator,
            $this->jsonPointer,
            $this->jsonDecoder ?: new ExtJsonDecoder()
        );
    }

    /**
     * @param string $string
     *
     * @return self
     *
     * @throws InvalidArgumentException
     */
    public static function fromString($string, array $options = [])
    {
        return new self(new StringChunks($string), $options);
    }

    /**
     * @param string $file
     *
     * @return self
     *
     * @throws Exception\InvalidArgumentException
     */
    public static function fromFile($file, array $options = [])
    {
        return new self(new FileChunks($file), $options);
    }

    /**
     * @param resource $stream
     *
     * @return self
     *
     * @throws Exception\InvalidArgumentException
     */
    public static function fromStream($stream, array $options = [])
    {
        return new self(new StreamChunks($stream), $options);
    }

    /**
     * @param iterable $iterable
     *
     * @return self
     *
     * @throws Exception\InvalidArgumentException
     */
    public static function fromIterable($iterable, array $options = [])
    {
        return new self($iterable, $options);
    }

    /**
     * @return \Generator
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return $this->parser->getIterator();
    }

    public function getTokensIterator(){
        return $this->tokensIterator;
    }

    public function getPosition()
    {
        return $this->parser->getPosition();
    }

    public function getJsonPointers(): array
    {
        return $this->parser->getJsonPointers();
    }

    public function getCurrentJsonPointer(): string
    {
        return $this->parser->getCurrentJsonPointer();
    }

    public function getMatchedJsonPointer(): string
    {
        return $this->parser->getMatchedJsonPointer();
    }
    
    public function getCurrentKey(): string
    {
        return $this->parser->getCurrentKey();
    }
    
    public function getCurrentToken(): string
    {
        return $this->parser->getCurrentToken();
    }

    public function getLastToken(): string
    {
        return $this->parser->getLastToken();
    }
    
    public function getCurrentJsonBuffer(): string
    {
        return $this->parser->getCurrentJsonBuffer();
    }

    /**
     * @return bool
     */
    public function isDebugEnabled()
    {
        return $this->debugEnabled;
    }
}
