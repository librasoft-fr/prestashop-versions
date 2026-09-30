<?php

declare(strict_types=1);

namespace Sentry;


final class UserDataBag
{
    
    private $id;

    
    private $email;

    
    private $ipAddress;

    
    private $username;

    
    private $segment;

    
    private $metadata = [];

    
    public function __construct(
        $id = null,
        ?string $email = null,
        ?string $ipAddress = null,
        ?string $username = null,
        ?string $segment = null
    ) {
        $this->setId($id);
        $this->setEmail($email);
        $this->setIpAddress($ipAddress);
        $this->setUsername($username);
        $this->setSegment($segment);
    }

    
    public static function createFromUserIdentifier($id): self
    {
        return new self($id);
    }

    
    public static function createFromUserIpAddress(string $ipAddress): self
    {
        return new self(null, null, $ipAddress);
    }

    
    public static function createFromArray(array $data): self
    {
        $instance = new self();

        foreach ($data as $field => $value) {
            switch ($field) {
                case 'id':
                    $instance->setId($value);
                    break;
                case 'ip_address':
                    $instance->setIpAddress($value);
                    break;
                case 'email':
                    $instance->setEmail($value);
                    break;
                case 'username':
                    $instance->setUsername($value);
                    break;
                case 'segment':
                    $instance->setSegment($value);
                    break;
                default:
                    $instance->setMetadata($field, $value);
                    break;
            }
        }

        return $instance;
    }

    
    public function getId()
    {
        return $this->id;
    }

    
    public function setId($id): self
    {
        if ($id !== null && !\is_string($id) && !\is_int($id)) {
            throw new \UnexpectedValueException(\sprintf('Expected an integer or string value for the $id argument. Got: "%s".', get_debug_type($id)));
        }

        $this->id = $id;

        return $this;
    }

    
    public function getUsername(): ?string
    {
        return $this->username;
    }

    
    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    
    public function getEmail(): ?string
    {
        return $this->email;
    }

    
    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    
    public function getSegment(): ?string
    {
        return $this->segment;
    }

    
    public function setSegment(?string $segment): self
    {
        $this->segment = $segment;

        return $this;
    }

    
    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    
    public function setIpAddress(?string $ipAddress): self
    {
        if ($ipAddress !== null) {
            
            if (strpos($ipAddress, '[') === 0 && substr($ipAddress, -1) === ']') {
                $ipAddress = substr($ipAddress, 1, -1);
            }

            if (filter_var($ipAddress, \FILTER_VALIDATE_IP) === false) {
                $client = SentrySdk::getCurrentHub()->getClient();

                if ($client !== null) {
                    $client->getOptions()->getLoggerOrNullLogger()->debug(
                        \sprintf('The "%s" value is not a valid IP address.', $ipAddress)
                    );
                }

                return $this;
            }
        }

        $this->ipAddress = $ipAddress;

        return $this;
    }

    
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    
    public function setMetadata(string $name, $value): self
    {
        $this->metadata[$name] = $value;

        return $this;
    }

    
    public function removeMetadata(string $name): self
    {
        unset($this->metadata[$name]);

        return $this;
    }

    
    public function merge(self $other): self
    {
        $this->id = $other->id;
        $this->email = $other->email;
        $this->ipAddress = $other->ipAddress;
        $this->username = $other->username;
        $this->segment = $other->segment;
        $this->metadata = array_merge($this->metadata, $other->metadata);

        return $this;
    }
}
