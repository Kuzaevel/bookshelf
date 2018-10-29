<?php

namespace App;

use Slim\Views\Twig;
use Slim\Container;
use PDO;
//use Slim\Http\Request;
//use Slim\Http\Response as Response;

use App\Model;


class Controller
{
    private $view;
    private $container;
    private $model;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->view = $container->get('view');
    }

    // список книг
    public function listBooks($request,$response)
    {
        $model = new Model($this->container);
        $listBooks = $model->getListBooks();
        $data = array('books' => $listBooks);
        return $this->view->render($response, 'index.html.twig',$data);
    }

    public function newBook($request, $response)
    {   // открытие формы добавления книги - get
        if($request->isGet()) {
            $response = $this->view->render($response, 'add.html.twig');
            return $response;

        } else if($request->isPost()){
            // запись новой книги в базу
            $formData = $request->getParsedBody();
            $model = new Model($this->container);
            $model->addBook($formData);

            $listBooks = $model->getListBooks();
            $data = array('books' => $listBooks);
            $response = $this->view->render($response, 'index-table.html.twig',$data);
        }
        return $response;
    }

    //удаление выбранной книги
    public function deleteBook($request, $response, $args)
    {
        $id = (int) $args['id'];
        $model = new Model($this->container);
        $model->removeBook($id);
        return $response->withRedirect('/');
    }

    public function viewBook($request, $response, $args)
    {
        $id = (int) $args['id'];
        $model = new Model($this->container);
        $book = $model->getOneBook($id);
        $data = array('book'=>$book);
        return $this->view->render($response, 'view.html.twig', $data);
    }

    public function editBook($request, $response, $args)
    {
        $id = (int) $args['id'];
        $model = new Model($this->container);
        if($request->isGet()) {
            $data = array('book' => $model->getOneBook($id));
            $response = $this->view->render($response, 'edit.html.twig',$data);
        } else if($request->isPost()) {
            $formData = $request->getParsedBody();
            $formData['id'] = $id;
            $model->updateOneBook($formData);
            $response = $response->withRedirect('/');
        }
        return $response;
    }

}