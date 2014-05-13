CSV Response
=============

Use:

```php
class SomePresenter extends BasePresenter
{
    public function actionDefault()
    {
        $data = [
            [ 'name' => 'George', 'age' => 15, 'grade' => 2, ],
            [ 'name' => 'Jack', 'age' => 17, 'grade' => 4, ],
            [ 'name' => 'Mary', 'age' => 17, 'grade' => 1, ],
        ];

        $response = new \Nette\Application\Responses\CsvResponse($data, 'students.csv');
        $this->sendResponse( $response );
    }
}
```

Individual settings example:

```php
use Nette\Application\Responses\CsvResponse;

$response
	->setGlue( CsvResponse::SEMICOLON )
	->setOutputCharset( 'cp1250' )
	->setContentType( 'application/csv' )
	->setHeadingFormatter( 'strtoupper' )
	->setDataFormatter( 'trim' )
;
```
