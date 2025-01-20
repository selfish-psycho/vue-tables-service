<?php

namespace App\Shared\DI;

use Bitrix\Main\ArgumentException;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;

/**
 * Класс для генерации контейнеров.
 * @package App\Shared\DI
 */
class Container implements ContainerInterface
{
    private array $objects = [];

    /**
     * @inheritDoc
     */
    public function has(string $id): bool
    {
        return isset($this->objects[$id]) || class_exists($id);
    }

    /**
     * Метод возвращает инстанс класса по его ключу.
     *
     * @param string $id
     * @return mixed
     * @throws ArgumentException
     */
    public function get(string $id): mixed
    {
        try {
            return
                isset($this->objects[$id])
                    ? $this->objects[$id]()      // "Старый подход"
                    : $this->prepareObject($id); // "Новый" подход

        } catch (ContainerExceptionInterface|NotFoundExceptionInterface|Exception $exception) {
            throw new ArgumentException($exception->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    private function prepareObject(string $class): object
    {
        try {


            $classReflector = new ReflectionClass($class);

            // Получаем рефлектор конструктора класса, проверяем - есть ли конструктор
            // Если конструктора нет - сразу возвращаем экземпляр класса
            $constructReflector = $classReflector->getConstructor();
            if (empty($constructReflector)) {
                return new $class;
            }

            // Получаем рефлекторы аргументов конструктора
            // Если аргументов нет - сразу возвращаем экземпляр класса
            $constructArguments = $constructReflector->getParameters();
            if (empty($constructArguments)) {
                return new $class;
            }

            // Перебираем все аргументы конструктора, собираем их значения
            $args = [];
            foreach ($constructArguments as $argument) {
                // Получаем тип аргумента
                $argumentType = $argument->getType()->getName();
                // Получаем сам аргумент по его типу из контейнера
                $args[$argument->getName()] = $this->get($argumentType);
            }

            // И возвращаем экземпляр класса со всеми зависимостями
            return new $class(...$args);
        } catch (ReflectionException|ContainerExceptionInterface|NotFoundExceptionInterface $e) {
            throw new Exception($e->getMessage());
        }
    }
}
