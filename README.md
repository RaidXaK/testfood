Реализация задачи процедурным кодом и ООП.

 Необходимо разработать простой конструктор блюд.
В приложенном архиве дамп базы данных, содержащий исходные данные для конструктора. В базе содержится две таблицы
•	В таблице ingredient_type содержатся типы возможных ингредиентов. Каждому типу соответствует уникальный 1-буквенный код
•	В таблице ingredient хранятся конкретные ингредиенты с ценой
На вход конструктора поступает строка, содержащая коды ингредиентов, которые должны входит в полученное блюдо. Один ингредиент может быть указан несколько раз. Например, строка «dcciii» означает блюдо, состоящее из одного теста, двух видов сыра и трёх видов начинки.
Необходимо сформировать набор всех возможных комбинаций ингредиентов, соответствующих заданному шаблону. При этом один ингредиент не может встречаться в блюде дважды.
Результатом работу конструктора должен быть JSON-массив, содержащий все возможные комбинации. Пример вывода для входной строки “dcii”:
[
	{
“products”: [
	{“type”:”Тесто”,”value”:”Тонкое тесто”},
	{“type”:”Сыр”,”value”:”Моцарелла”},
	{“type”:”Начинка”,”value”:”Ветчина”},
	{“type”:”Начинка”,”value”:”Колбаса”},
],
“price”: 215
},
{
“products”: [
	{“type”:”Тесто”,”value”:”Тонкое тесто”},
	{“type”:”Сыр”,”value”:”Моцарелла”},
	{“type”:”Начинка”,”value”:”Ветчина”},
	{“type”:”Начинка”,”value”:” Грибы”},
],
“price”: 235
},
….
]
