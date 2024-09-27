<?php
/** @var \Romanzaycev\Tooolooop\Scope\Scope $this */
/** @var bool $is_authorized */
/** @var string $login */
/** @var string|null $error */
/** @var string|null $csrf_token */

$this->extend("layouts/base", [
    "title" => "Protected area",
]);
?>

<?php if (isset($error) && $error): ?>
    <p style="color: red">
        <?=$this->e($error)?>
    </p>
<?php endif; ?>

<?php if (!$is_authorized): ?>
    <h3>Access denied!</h3>

    <style>
        .row {
            margin: 10px 0;
        }
    </style>

    <form action="/protected" method="post">
        <div class="row">
            <label for="login">Login:</label>
            <div>
                <input type="text" name="login" value="user" id="login"/>
            </div>
        </div>

        <div class="row">
            <label for="password">Password:</label>
            <div>
                <input type="password" name="password" value="password" id="password" />
            </div>
        </div>

        <p>
            <button type="submit">Login</button>
        </p>
    </form>
<?php else: ?>
    <h3>Welcome, <?=$this->e($login)?>!</h3>

    <br />

    <form action="/protected/logout" method="post">
        <input type="hidden" name="csrf-token" value="<?=$this->e($csrf_token)?>" />
        <button type="submit">Logout</button>
    </form>
<?php endif; ?>
