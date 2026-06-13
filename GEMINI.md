# PHP Aggregate Root Template

<?php

declare(strict_types=1);

namespace App\Domain\User\Entity;

use App\Domain\Common\AggregateRoot;
use App\Domain\User\Event\UserWasRegistered;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\HashedPassword;

final class User extends AggregateRoot
{
    private function __construct(
        private UserId $id,
        private Email $email,
        private HashedPassword $password,
        private UserStatus $status,
    ) {
        $this->addDomainEvent(new UserWasRegistered($this->id));
    }

    public static function register(
        UserId $id,
        Email $email,
        PlainPassword $plainPassword,
        PasswordHasherInterface $hasher // Сервис из Domain, не инфраструктура!
    ): self {
        return new self(
            $id,
            $email,
            $hasher->hash($plainPassword),
            UserStatus::active()
        );
    }

    public function changeEmail(Email $newEmail): void
    {
        if ($this->status->isBlocked()) {
            throw new \DomainException('Blocked user cannot change email');
        }
        $this->email = $newEmail;
    }

    // Никогда: public function setEmail(Email $email): void { ... }
}


# PHP DDD + Clean Architecture Constitution

## 1. Strict Layers (Нарушение = Ошибка)
- **Domain:** НЕ имеет зависимостей. Никаких `use Illuminate\Database\Eloquent\Model`, `use Doctrine\ORM\Mapping`.
- **Application:** Зависит ТОЛЬКО от Domain (интерфейсы репозиториев, сервисы).
- **Infrastructure:** Реализует интерфейсы из Domain/Application.
- **Presentation:** Самый внешний слой (Controllers, CLI).

## 2. Запрещенные практики в Domain
- `new DateTime()` → используйте `DateTimeImmutable` или Value Object.
- Массив как результат метода (`array`). Всегда типизированная коллекция (`UserCollection`).
- Публичные сеттеры. Мутация только через методы-глаголы (`changeEmail()`, `activate()`).
- Статические вызовы. Domain должен быть тестируем без моков глобального состояния.

## 3. Обязательные паттерны
- **Value Object:** `readonly class Email` (PHP 8.2+), `private readonly` свойства, создание через named constructor `fromString()`.
- **Aggregate Root:** Гарантирует инварианты. Пример: `class User extends AggregateRoot { private function __construct(...) ... }`.
- **Repository:** Интерфейс в Domain (`UserRepositoryInterface`), реализация в Infrastructure (`DoctrineUserRepository`).
- **Use Case:** Класс `class RegisterUserUseCase { public function execute(RegisterUserCommand $command): UserDto }`.

## 4. Обработка ошибок
- Доменные исключения (`DomainException`) → код 422/400.
- Прикладные ошибки (`ApplicationException`) → код 409/403.
- Никогда не выбрасывать `RuntimeException` из Use Case.

## 5. Имена классов и неймспейсы
- Domain: `App\Domain\User\Entity\User`, `App\Domain\User\ValueObject\Email`
- Application: `App\Application\User\RegisterUser\RegisterUserUseCase`
- Infrastructure: `App\Infrastructure\Persistence\Doctrine\UserRepository`


# PHP Value Object Template (Immutable)

<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject;

final readonly class Email
{
    private function __construct(
        private string $value
    ) {
        $this->validate();
    }

    public static function fromString(string $email): self
    {
        return new self($email);
    }

    private function validate(): void
    {
        if (!filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            throw new \DomainException('Invalid email format');
        }
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}