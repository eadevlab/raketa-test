### Ревью

### [schema.init.sql](migrations/schema.init.sql) `migrations/schema.init.sql`

- Длина uuid 36 символов. Нет смысла делать поле `varchar(255)`
- Для поля is_active tinyint лучше указать длину 1
- Индексация is_active излишня
- В SQL ключевые слова принято писать в UPPERCASE
- uuid можно сделать уникальным индексом, т.к. часто выполняем поиск по этому полю и по логике оно должно быть уникально
- (?) `category varchar(255)` категории товаров лучше хранить в отдельной таблице
- (?) `thumbnail varchar(255)` ссылка к файлу может быть длинее 255 символов
- (?) Лишние пробелы в файле


### [AddToCartController.php](src/Controller/AddToCartController.php) `(src/Controller/AddToCartController.php)`

```php 
public function get(RequestInterface $request): ResponseInterface
```
Нелогичное название метода `public function get`
Более подходящие: 
- Под типу запроса: post
- Пол логике работы: add

```php
$product = $this->productRepository->getByUuid($rawRequest['productUuid']);
```
- Отсутствует проверка существования `$product`
- Отсутствует проверка активности товара
```php
        $cart->addItem(new CartItem(
            Uuid::uuid4()->toString(),
            $product->getUuid(),
            $product->getPrice(),
            $rawRequest['quantity'],
        ));
```
После добавления не хватает сохранения корзины
```php
        return $response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus(200);
```
Header можно задать как дефолтный для JsonResponse, устанавливать статус лучше тоже только в случае ошибки, по умолчанию: 200

```php
'status' => 'success'
```
Решение на любителя, статусом выполнения можно оперировать через код ответа

```php
declare(strict_types = 1);
```
Пропущен в файле

### [GetCartController.php](src/Controller/GetCartController.php) `(src/Controller/GetCartController.php)`
```php
if (! $cart) {
```
Лишний плобел после `!`

```php
            $response->getBody()->write(
                json_encode(
                    ['message' => 'Cart not found'],
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
                )
            );

            return $response
                ->withHeader('Content-Type', 'application/json; charset=utf-8')
                ->withStatus(404);
```
Тут лишний `return $response` Ниже по коду так же есть return response, лишнее дублирование кода.

```php
    return $response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus(404);
```
Даже в случае успешного получения корзины код 404. Ошибочка
Ну и про вечную установку заголовка писал уже

```php
    public function __construct(
        public CartView $cartView,
        public CartManager $cartManager
    ) {
    }
```
Область видимости аттрибутов можно сделать и private, public ничем не оправдан

### [Cart.php](src/Domain/Cart.php) `(src/Domain/Cart.php)`
```php
readonly private Customer $customer,
```
Должен быть не обязательным и не readonly параметром, т.к. корзина может быть и у анонимного пользователя
Необходимо добавить setter
```php
readonly private string $paymentMethod
```
Так же должен быть не обязательным и не readonly
Необходимо добавить setter

```php
        private array $items,
```
Добавить значение по умолчанию
Убрать запятую после последнего аттрибута

### [CartItem.php](src/Domain/CartItem.php) `(src/Domain/CartItem.php)`

```php
        public string $uuid,
        public string $productUuid,
        public float $price,
        public int $quantity,
```
Область видимость лучше private
Запятая после quantity всё-таки лишняя

```php
public int $quantity
```
Количество товара параметр переменчивый, соответственно не может быть readonly (у класса аттрибут указан)
Необходимо добавить setter

### [Customer.php](src/Domain/Customer.php) `(src/Domain/Customer.php)`
```php
        public int $quantity,
    ) {
    }
```
Лишняя запятая

### [Connector.php](src/Infrastructure/Connector.php) `(src/Infrastructure/Connector.php)`

Сам класс напрямую зависит от классов Cart и Redis что архитектурно неверно, вдруг захочется быстро перейти с redis, например, на KeyDB

```php
public function __construct($redis)
```
Отсутствует тип для $redis

```php
    public function get(Cart $key)
```
Неверный тип данных, должен быть `string`

```php
    public function has($key): bool
```
Отсутствует тип данных

```php
    public function set(string $key, Cart $value)
```
Очень хочется добавить параметр expire

### [Product.php](src/Repository/Entity/Product.php) `(src/Repository/Entity/Product.php)`

- Нелогичное расположение в папке RepositoryEntity, лучше перенести в Domain
```php
readonly class Product
```
Не хватает модификатора final

```php
        private string $description,
        private string $thumbnail,
```
Не учитывается что данные параметры могут принимать значение null

### [CartManager.php](src/Repository/CartManager.php) `(src/Repository/CartManager.php)`
```php
    public $logger;
```
Отсутсвует тип переменной
```php
            $this->logger->error('Error');
```
- Исходя из кода логгер не обязательный параметр необходимо или добавить проверку что логгер определен или переместить логгер в контроллер.
- Ошибка не информативна

```php
        return new Cart(session_id(), []);
```
Ошибка инициализации, не заполнены обязательные параметры

### [ProductRepository.php](src/Repository/ProductRepository.php) `(src/Repository/ProductRepository.php)`
```php
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
```
Можно объявить $connection сразу в конструкторе, будет более лаконично

```php
        $row = $this->connection->fetchOne(
            "SELECT * FROM products WHERE uuid = " . $uuid,
        );
```
Плохой подход, открывает возможности для инъекций. Лучше переделать так:
```php
        $row = $this->connection->fetchOne(
            "SELECT * FROM products WHERE uuid = :uuid",
            [
                'uuid' => $uuid,
            ]
        );
```

```php
        if (empty($row)) {
            throw new Exception('Product not found');
        }
```
Ошибка, отсутсвует импорт класса Exception, или хотя бы вызов таким образом: `\Exception(...)`

```php
        return array_map(
            static fn (array $row): Product => $this->make($row),
            $this->connection->fetchAllAssociative(
                "SELECT id FROM products WHERE is_active = 1 AND category = " . $category,
            )
        );
```
- Та же история с параметрами как и описано выше.
- Выбираются только идентификаторы товаров, а далее идёт инициализация сущности товара со всеми параметрами, не логично. Необходимо или выбрать все поля `*` или же указывать в select все поля которые необходимы

### [CartView.php](src/View/CartView.php) `(src/View/CartView.php)`
```php
            'customer' => [
                'id' => $cart->getCustomer()->getId(),
                'name' => implode(' ', [
                    $cart->getCustomer()->getLastName(),
                    $cart->getCustomer()->getFirstName(),
                    $cart->getCustomer()->getMiddleName(),
                ]),
                'email' => $cart->getCustomer()->getEmail(),
            ],
```
- Параметр сustomer в корзине как мы помним не передаётся и по факту является не обязательным параметром, необходимо добавить проверку существования.
```php
$product = $this->productRepository->getByUuid($item->getProductUuid());
```
Плохая идея, на каждый товар в корзине будет дёргаться отдельный запрос. Необходимо добавить в ProductRepository метод для получения товаров по массиву uuid

```php
           $total += $item->getPrice() * $item->getQuantity();
            $product = $this->productRepository->getByUuid($item->getProductUuid());

            $data['items'][] = [
                'uuid' => $item->getUuid(),
                'price' => $item->getPrice(),
                'total' => $total,
```
- А вот тут ошибка! переменная $total хранит в себе сумму по всей корзине и с каждой итерацией увеличивается, а для товара параметр total должен отображать общую сумму за товар
- Тут ещё вопрос зачем 2 раза price у элемента корзины и товара, пока товар лежит в корзине его цена может изменится, но допустим нам надо знать с какой ценой добавляли


## Что сделано:

- Рефакторинг
- Переписал немного коннектор к редису