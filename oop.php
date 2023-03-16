<?php
require(__DIR__ . '\..\bitrix\modules\main\include\prolog_before.php'); //подключение функционала bitrix для дальнейшей работы с бд
//require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php'); //подключение функционала bitrix для дальнейшей работы с бд

class Ingredient implements JsonSerializable
{
    private int $id;

    private string $code;

    private string $type;

    private string $title;

    private float $price;

    public function __construct(
        int $id,
        string $code,
        string $type,
        string $title,
        float $price
    ) {
        $this->id = $id;
        $this->code = $code;
        $this->type = $type;
        $this->title = $title;
        $this->price = $price;
    }

    public static function fromArray(array $data): Ingredient
    {
        return new Ingredient(
            $data['id'],
            $data['code'],
            $data['type'],
            $data['title'],
            $data['price'],
        );
    }

    public function getId(): int
    {
        return  $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function jsonSerialize(): array
    {
        return ['type' => $this->type, 'value'=> $this->title];
    }
}

//класс для выгрузки данных из базы
class IngredientRepository
{
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    /**
     * @param string[] $codes
     *
     * @return array<string, array<int, Ingredient>>
     */
    public function getIngredientsByCodes(array $codes_array): array
    {
        $result = [];
        $codes = implode("','", $codes_array);
        $query = "SELECT T2.id, T1.code, T1.title as type, T2.title, T2.price FROM ingredient_type as T1 JOIN ingredient as T2 on T1.id = T2.type_id WHERE T1.code in ('".$codes."')";
        $recordset = $this->connection->query($query); // тут выполняешь запрос и получаешь массив

        while ($row = $recordset->fetch()) {
            $result[$row['code']][] = Ingredient::fromArray($row);
        }

        return $result;
    }
}

// класс с готовой выгрузкой из базы всех ингридиентов для теста
class IngredientRepositoryTest
{
    public function getIngredientsByCodes(array $codes_array): array
    {
        $result = [];
        $recordset = json_decode('{"d":[{"id":"1","code":"d","type":"Тесто","title":"Тонкое тесто","price":"100.00"},{"id":"2","code":"d","type":"Тесто","title":"Пышное тесто","price":"110.00"},{"id":"3","code":"d","type":"Тесто","title":"Ржаное тесто","price":"150.00"}],"c":[{"id":"4","code":"c","type":"Сыр","title":"Моцарелла","price":"50.00"},{"id":"5","code":"c","type":"Сыр","title":"Рикотта","price":"70.00"}],"i":[{"id":"6","code":"i","type":"Начинка","title":"Колбаса","price":"30.00"},{"id":"7","code":"i","type":"Начинка","title":"Ветчина","price":"35.00"},{"id":"8","code":"i","type":"Начинка","title":"Грибы","price":"50.00"},{"id":"9","code":"i","type":"Начинка","title":"Томаты","price":"10.00"}]}');
        foreach ( $recordset as $k =>$row) {
            foreach($row as $ing) {
                $result[$k][] = Ingredient::fromArray((array)$ing);
            }
        }
        return $result;
    }
}

class UniqueProductsGenerator
{
    private IngredientRepositoryTest $ingredientRepository;

    public function __construct(IngredientRepositoryTest $ingredientRepository) {
        $this->ingredientRepository = $ingredientRepository;
    }

    /**
     * @param string[] $codes
     *
     * @return Product[]
     */
    public function generateProductsByIngredientCodes(array $codes): array
    {
        $products = [];
        $uniqueCodes = array_unique($codes);
        $ingredients = $this->ingredientRepository->getIngredientsByCodes($uniqueCodes);
        foreach ($codes as $code) {
            $products = $this->prepareProducts($products, $ingredients[$code]);
        }

        return $products;
    }

    private function prepareProducts(array $products, array $ingredients): array
    {
        $temp_result = [];
        if (empty($products)) {
            foreach ($ingredients as $ing) {
                $temp_result[] = new Product([$ing]);
            }
        }
        else {
            foreach ($products as $product) {
                foreach ($ingredients as $ing) {
                    if($product->hasIngredient($ing)){
                        continue;
                    }
                    $new_product = $product->addIngredient($ing);
                    $temp_result[$new_product->getCode()] = $new_product;
                }
            }
        }
        return $temp_result;
    }
}

class Product implements JsonSerializable {

    /** @var array<string, Ingredient> */
    private array $ingredients;

    /**
     * @param Ingredient[] $ingredients
     */
    public function __construct(array $ingredients = [])
    {
        foreach ($ingredients as $ingredient) {
            $this->ingredients[$ingredient->getId()] = $ingredient;
        }
    }

    public function addIngredient(Ingredient $ingredient): Product
    {
        return new Product([...$this->ingredients, $ingredient]);
        if (isset($this->ingredients[$ingredient->getId()])) {
            return false;
        }

        $this->ingredients[$ingredient->getId()] = $ingredient;

        return true;
    }

    public function getPrice(): float
    {
        return array_reduce(
            array_map(static fn (Ingredient $ingredient): float => $ingredient->getPrice(), $this->ingredients),
            static fn (float $sum, float $price) => $sum + $price,
            0,
        );
    }

    public function getCode(): string
    {
        $ingredientCodes = array_map(static fn (Ingredient $ingredient): string => (string)$ingredient->getId(), $this->ingredients);
        sort($ingredientCodes);

        return implode($ingredientCodes);
    }

    public function isEqual(Product $product): bool
    {
        return $this->getCode() === $product->getCode();
    }

    public function hasIngredient(Ingredient $ingredient): bool
    {
        return isset($this->ingredients[$ingredient->getId()]);
    }

    public function jsonSerialize(): array
    {
        return ['product' => array_values($this->ingredients), 'price'=> $this->getPrice()];
    }
}

$input = "dcciii"; //тест из скрипта
//$input = $argv[1]; // из консоли

$codes_array = str_split($input);

//$ing_rep = new IngredientRepository(Bitrix\Main\Application::getConnection()); // основной класс
$ing_rep = new IngredientRepositoryTest(); // класс с готовой базой

$data = new UniqueProductsGenerator($ing_rep);
$data = $data->generateProductsByIngredientCodes($codes_array);

echo json_encode(array_values($data), JSON_UNESCAPED_UNICODE);

