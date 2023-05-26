<?php

namespace guzzle;

class RknResponse
{
    protected array $isBlocked;
    protected ?\Exception $exception;


    public function setIsBlocked(array $value): self {
        $this->isBlocked = $value;
        return $this;
    }
    //Устанавливает заблокирован ли сайт

    public function isBlocked(): array
    {
        return $this->isBlocked;
    }
    //Возвращает заблокирован ли сайт

    public function setException(\Exception $exception): self
    {
        $this->exception = $exception;
        return $this;
    }
    //Устанавливает ошибку


    public function getException(): ?\Exception
    {
        return $this->exception;
    }
    //Возвращает ошибку

    public function hasException(): bool
    {
        return $this->exception !== null;
    }
    // Показывает, есть ли ошибка
}