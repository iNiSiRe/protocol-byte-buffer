<?php

namespace inisire\Protocol;


class ByteBuffer
{
    private const UNSIGNED_SHORT = 65535;
    private const UNSIGNED_INT = 4294967295;
    private const UNSIGNED_LONG = 18446744073709551614;

    public function __construct(
        private string $data,
        private int $position = 0
    )
    {
    }

    public function read(int $count): string
    {
        if ($count > $this->getRemainingBytes()) {
            throw new \OutOfRangeException(sprintf('Buffer has only %d bytes remaining but trying to read %d bytes.', $this->getRemainingBytes(), $count));
        }

        $chunk = substr($this->data, $this->position, $count);
        $this->position += strlen($chunk);

        return $chunk;
    }

    public function readByte(): int
    {
        return ord($this->read(1));
    }

    public function readUnsignedShort(): int
    {
        return ($this->readByte() << 8) | $this->readByte();
    }

    public function readUnsignedInt(): int
    {
        return ($this->readByte() << 24) | ($this->readByte() << 16) | ($this->readByte() << 8) | $this->readByte();
    }

    public function write(string $value): void
    {
        $this->data .= $value;
    }

    public function writeByte(int $value): void
    {
        $this->write(chr($value));
    }

    public function writeUnsignedShort(int $value): void
    {
        if ($value > self::UNSIGNED_SHORT) {
            throw new \InvalidArgumentException(sprintf('USHORT max value is "%s", but "%s" given.', self::UNSIGNED_SHORT, $value));
        }

        $this->write(chr(($value & 0xFFFF) >> 8));
        $this->write(chr($value & 0xFF));
    }

    public function writeUnsignedInt(int $value): void
    {
        if ($value > self::UNSIGNED_INT) {
            throw new \InvalidArgumentException(sprintf('UINT max value is "%s", but "%s" given.', self::UNSIGNED_INT, $value));
        }

        $this->write(chr(($value & 0xFFFFFFFF) >> 24));
        $this->write(chr(($value & 0xFFFFFF) >> 16));
        $this->write(chr(($value & 0xFFFF) >> 8));
        $this->write(chr($value & 0xFF));
    }

    public function size(): int
    {
        return strlen($this->data);
    }

    public function getRemainingBytes(): int
    {
        return $this->size() - $this->position;
    }

    public function flush(): void
    {
        $this->data = substr($this->data, $this->position);
        $this->position = 0;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function __toString()
    {
        return $this->data;
    }
}
