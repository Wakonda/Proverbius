<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\Loader\YamlFileLoader;

// Register service providers.
$app->register(new Silex\Provider\ServiceControllerServiceProvider());

$app->register(new Silex\Provider\DoctrineServiceProvider());
$app->register(new Silex\Provider\FormServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\RoutingServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider());
$app->register(new Silex\Provider\SwiftmailerServiceProvider());
$app->register(new Silex\Provider\HttpFragmentServiceProvider());
// 

$app['security.role_hierarchy'] = array(
    'ROLE_ADMIN' => array('ROLE_USER'),
);

$app['security.access_rules'] = array(
    array('^/admin', 'ROLE_ADMIN'),
);

$app['session.storage.options'] = [
    'name' => "Xkoedz"
];

$app['security.firewalls'] = array(
    'main' => array(
        'pattern' => '^/',
		'anonymous' => true,
		'remember_me' => array('key' => '3r9w8ByO2hSP0fAB'),
		'form' => array('login_path' => '/user/login', 'check_path' => '/admin/login_check','default_target_path'=> '/','always_use_default_target_path'=>true),
		'logout' => array('logout_path' => '/admin/logout'),
		'users' => function ($app) {
			return new Proverbius\Controller\UserProvider($app['db']);
		}
    )
);

$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => $app['security.firewalls'],
	'security.role_hierarchy' => $app['security.role_hierarchy'],
	'security.access_rules' => $app['security.access_rules'],
	'session.storage.options' => $app['session.storage.options']
));

$app->register(new Silex\Provider\RememberMeServiceProvider());

$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'locale' => 'fr'
));

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.orm.proxies_namespace'     => 'DoctrineProxy',
    'db.orm.auto_generate_proxies' => true,
    'db.orm.entities'              => array(array(
        'type'      => 'annotation',       // как определяем поля в Entity
        'path'      => __DIR__,   // Путь, где храним классы
        'namespace' => 'Proverbius\Entity', // Пространство имен
    ))
));

$app['security.default_encoder'] = function ($app) {
    return $app['security.encoder.digest'];
};

$app->before(function () use ($app) {
	$request = $app['request_stack']->getCurrentRequest();
	$request->setLocale($app['locale']);
	$app['translator']->setLocale($app['locale']);
});

$app->boot();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.options' => array(
        'cache' => isset($app['twig.options.cache']) ? $app['twig.options.cache'] : false,
        'strict_variables' => true,
    ),
    'twig.path' => array(__DIR__ . '/Proverbius/Resources/views')
));

$app['twig'] = $app->extend('twig', function (\Twig_Environment $twig) use ($app) {
	$twig->addExtension(new Proverbius\Service\ProverbiusExtension($app));
 
	return $twig;
});

$app['twig']->addGlobal("dev", 1);

// Register repositories.
$app['repository.proverb'] = function ($app) {
    return new Proverbius\Repository\ProverbRepository($app['db']);
};

// Register the error handler.
$app->error(function (\Exception $e, $code) use ($app) {

    if ($app['debug']) {
        return;
    }

    switch ($code) {
        case 404:
            $message = 'The requested page could not be found.';
            break;
        default:
            $message = 'We are sorry, but something went terribly wrong.';
    }
	
	return $app['twig']->render('Index/error.html.twig', array('code' => $code, 'message' => $e->getMessage()));
});

$app->before(function () use ($app) {
    $app['twig']->addGlobal('generic_layout', $app['twig']->loadTemplate('generic_layout.html.twig'));
});

// Register repositories
$app['repository.tag'] = function ($app) {
    return new Proverbius\Repository\TagRepository($app['db']);
};
$app['repository.country'] = function ($app) {
    return new Proverbius\Repository\CountryRepository($app['db']);
};
$app['repository.version'] = function ($app) {
    return new Proverbius\Repository\VersionRepository($app['db']);
};
$app['repository.user'] = function ($app) {
    return new Proverbius\Repository\UserRepository($app['db']);
};
$app['repository.contact'] = function ($app) {
    return new Proverbius\Repository\ContactRepository($app['db']);
};
$app['repository.vote'] = function ($app) {
    return new Proverbius\Repository\VoteRepository($app['db']);
};
$app['repository.comment'] = function ($app) {
	return new Proverbius\Repository\CommentRepository($app['db']);
};
$app['repository.page'] = function ($app) {
	return new Proverbius\Repository\PageRepository($app['db']);
};

// Register controllers
$app["controllers.index"] = function($app) {
    return new Proverbius\Controller\IndexController();
};

$app["controllers.tagadmin"] = function($app) {
    return new Proverbius\Controller\TagAdminController();
};

$app["controllers.countryadmin"] = function($app) {
    return new Proverbius\Controller\CountryAdminController();
};

$app["controllers.proverbadmin"] = function($app) {
    return new Proverbius\Controller\ProverbAdminController();
};

$app["controllers.useradmin"] = function($app) {
    return new Proverbius\Controller\UserAdminController();
};

$app["controllers.admin"] = function($app) {
    return new Proverbius\Controller\AdminController();
};

$app["controllers.contact"] = function($app) {
    return new Proverbius\Controller\ContactController();
};

$app["controllers.contactadmin"] = function($app) {
    return new Proverbius\Controller\ContactAdminController();
};

$app["controllers.versionadmin"] = function($app) {
    return new Proverbius\Controller\VersionAdminController();
};

$app["controllers.user"] = function($app) {
    return new Proverbius\Controller\UserController();
};

$app["controllers.vote"] = function($app) {
    return new Proverbius\Controller\VoteController();
};

$app["controllers.comment"] = function($app) {
    return new Proverbius\Controller\CommentController();
};

$app["controllers.sitemap"] = function($app) {
	return new Proverbius\Controller\SitemapController();
};

$app["controllers.pageadmin"] = function($app) {
	return new Proverbius\Controller\PageAdminController();
};

$app["controllers.send"] = function($app) {
	return new Proverbius\Controller\SendController();
};

// Register Services
$app['generic_function'] = function ($app) {
    return new Proverbius\Service\GenericFunction($app);
};

// Form extension
$app['form.type.extensions'] = $app->extend('form.type.extensions', function ($extensions) use ($app) {
    $extensions[] = new Proverbius\Form\Extension\ButtonTypeIconExtension();
    return $extensions;
});

// SwiftMailer
// See http://silex.sensiolabs.org/doc/providers/swiftmailer.html
$app['swiftmailer.options'] = array(
	'host' => 'smtp.gmail.com',
	'port' => 465,
    'username' => 'test@yopmail.com',
    'password' => 'test',
    'encryption' => 'ssl'
);

// Global
$app['web_directory'] = realpath(__DIR__."/../web");

return $app;