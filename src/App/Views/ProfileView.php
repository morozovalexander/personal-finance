<?php

namespace App\Views;

use App\Model\User;

class ProfileView
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var string
     */
    private $message;

    /**
     * @var int
     */
    private $rublesAmount;

    /**
     * @param User $user
     * @param int $rublesAmount
     * @param string $message
     */
    public function __construct(User $user, int $rublesAmount = 0, string $message = '')
    {
        $this->user = $user;
        $this->rublesAmount = number_format($rublesAmount / 100, 2);
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function render(): string
    {
        return <<<EOT
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Users profile</title>

    <!-- Bootstrap core CSS -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="bootstrap/css/starter-template.css" rel="stylesheet">
</head>

<body>

<nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">
    <a class="navbar-brand" href="#">Personal-finance</a>

    <div class="collapse navbar-collapse" id="navbarsExampleDefault">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a class="nav-link" href="/">Home <span class="sr-only">(current)</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/logout">Logout <span class="sr-only"></span></a>
            </li>
        </ul>
    </div>
</nav>

<main role="main" class="container">

    <div class="alert alert-info" role="alert">
         {$this->message}
    </div>
    <div class="starter-template">
        <h1>User profile</h1>
        <p class="lead">Username: {$this->user->getUsername()}</p>
        <p class="lead">Name: {$this->user->getFirstName()} {$this->user->getLastName()}</p>
        <p class="lead">Current rubles amount: {$this->rublesAmount}</p>
    </div>
    
    <div class="row justify-content-md-center">
        <div class="col-md-auto">
            <form action="/pull_money" method="POST">
                 <div class="form-group">
                        <label for="money-amount-input">Rubles amount to pull:</label>
                        <input name="money-amount" type="number" step="0.01" class="form-control" id="money-amount-input" placeholder="0">
                 </div>
                 <button class="btn btn-lg btn-primary btn-block" type="submit">Pull out</button>
            </form>
        </div>
    </div>
</main><!-- /.container -->

<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>
</body>
</html>

EOT;
    }
}
