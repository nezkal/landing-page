<?php
/**
 *
 * Landing Page para páginas de entrada
 * (c)  Artur Magalhães <nezkal@gmail.com>
 *
 * @author Artur Magalhães <nezkal@gmail.com>
 */

// defines the SITE_KEY in Google ReCaptcha
define('SITE_KEY', 'NONE');

// define the SECRET_KEY in Recaptcha
define('SECRET_KEY', 'NONE');

require "vendor/autoload.php";

$app = new \Silex\Application();

// Set if debug mode is true or false, (errors and exceptions are displayed on the screen)
$app['debug'] = true;

// Register the default path of twig templates
$app->register(new \Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/views',
));


// Mail configurations

$mailerOptions = array(
    'host' => 'host',
    'port' => '25',
    'username' => 'username@username.com.br',
    'password' => 'password',
    'encryption' => null,
    'auth_mode' => null
);

$app['swiftmailer.options'] = $mailerOptions;


// Service Providers
$app->register(new \Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new \Silex\Provider\FormServiceProvider());
$app->register(new \Silex\Provider\ValidatorServiceProvider());
$app->register(new \Silex\Provider\TranslationServiceProvider());
$app->register(new \Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\SwiftmailerServiceProvider());

// Only handler that processing the http requests (GET and POST)
$handleRequest = function (\Symfony\Component\HttpFoundation\Request $request) use ($app, $mailerOptions) {

    $data = [
        'name',
        'email',
        'phone',
        'message'
    ];

    // creates the symfony form component

    /** @var \Symfony\Component\Form\Form $form */
    $form = $app['form.factory']->createBuilder('form', $data)
        ->add('name', null, ['label' => 'Name'])
        ->add('email', 'email', ['label' => 'Email'])
        ->add('phone', null, ['label' => 'Phone Number'])
        ->add('message', 'textarea', ['label' => 'Message'])
        ->getForm();

    $form->handleRequest($request);

    $sended = $app['session']->getFlashBag()->get('sended') ?: false;
    $error = null;

    $recaptcha = new \ReCaptcha\ReCaptcha(SECRET_KEY);
    $responseCaptcha = $recaptcha->verify($request->get('g-recaptcha-response', null));

    // verify the captcha is correct

    if ($form->isValid() && $responseCaptcha->isSuccess()) {

        $from = $mailerOptions['username'];
        $formData = $form->getData();

        $dataView = array('message' => array(
            'contactName' => $formData['name'],
            'created' => new \DateTime('now'),
            'message' => $formData['message'],
            'email' => $formData['email']
        ));

        $body = $app['twig']->render('email/contact.html.twig', $dataView);

        $message = \Swift_Message::newInstance()
            ->setSubject('<Site> Message Contact')
            ->setFrom(array($from => 'Web Site'))
            ->setBody($body, 'text/html');

        $app['mailer']->send($message);
        $app['session']->getFlashBag()->add('sended', true);

        return new \Symfony\Component\HttpFoundation\RedirectResponse('/');

    } elseif ($request->getMethod() == 'POST' && !$responseCaptcha->isSuccess()) {
        $error = true;
    }

    return $app['twig']->render('default.html.twig', array(
        'form' => $form->createView(),
        'sitekey' => SITE_KEY,
        'error' => $error,
        'sended' => $sended
    ));
};


$app->get('/', $handleRequest);
$app->post('/', $handleRequest);


$app->run();