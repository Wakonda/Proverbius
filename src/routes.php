<?php
// MAIN
$app->get('/', 'controllers.index:indexAction')
    ->bind('index');

$app->post('/search', 'controllers.index:indexSearchAction')
    ->bind('index_search');

$app->get('/read/{id}/{slug}', 'controllers.index:readAction')
	->bind('read');

$app->get('/read_pdf/{id}/{slug}', 'controllers.index:readPDFAction')
	->bind('read_pdf');

$app->get('/result_search/{search}', 'controllers.index:indexSearchDatatablesAction')
    ->bind('index_search_datatables');

$app->get('/error/{code}', 'controllers.index:errorAction')
	->bind('error');

$app->get('/last', 'controllers.index:lastAction')
	->bind('last');

$app->get('/stat', 'controllers.index:statAction')
	->bind('stat');

$app->get('/country/{id}/{slug}', 'controllers.index:countryAction')
	->bind('country');

$app->get('/country_datatables/{countryId}', 'controllers.index:countryDatatablesAction')
	->bind('country_datatables');

$app->get('/bycountries', 'controllers.index:byCountriesAction')
    ->bind('bycountries');

$app->get('/bycountries_datatables', 'controllers.index:byCountriesDatatablesAction')
    ->bind('bycountries_datatables');

$app->get('/byletters', 'controllers.index:byLettersAction')
    ->bind('byletters');

$app->get('/byletters_datatables', 'controllers.index:byLettersDatatablesAction')
    ->bind('byletters_datatables');

$app->get('/letter/{letter}', 'controllers.index:letterAction')
	->bind('letter');

$app->get('/letter_datatables/{letter}', 'controllers.index:letterDatatablesAction')
	->bind('letter_datatables');

$app->get('/page/{name}', 'controllers.index:pageAction')
	->bind('page_display');

$app->get('/admin', 'controllers.admin:indexAction')
	->bind('admin');

$app->get('/country_datatables/{countryId}', 'controllers.index:countryDatatablesAction')
	->bind('country_datatables');

// CONTACT
$app->get('/contact', 'controllers.contact:indexAction')
    ->bind('contact');

$app->post('/contact_send', 'controllers.contact:sendAction')
	->bind('contact_send');
	
// SEND
$app->get('send/index/{id}', 'controllers.send:indexAction')
	->assert('id', '\d+')
	->bind('send');

$app->post('send/send/{id}', 'controllers.send:sendAction')
	->assert('id', '\d+')
	->bind('send_go');

// SITEMAP
$app->get('/sitemap.xml', 'controllers.sitemap:sitemapAction')
    ->bind('sitemap');

$app->get('/generate_sitemap', 'controllers.sitemap:generateAction')
    ->bind('generate_sitemap');

// CAPTCHA
$app->get('/captcha', 'controllers.index:')
	->bind('captcha');

// GRAVATAR
$app->get('/gravatar', 'controllers.index:reloadGravatarAction')
	->bind('gravatar');

// COMMENT
$app->get('/comment/{id}', 'controllers.comment:indexAction')
	->assert('id', '\d+')
	->bind('comment');

$app->post('comment/create/{id}', 'controllers.comment:createAction')
	->assert('id', '\d+')
	->bind('comment_create');

$app->get('comment/load/{id}', 'controllers.comment:loadCommentAction')
	->assert('id', '\d+')
	->bind('comment_load');

// VOTE
$app->get('/vote/{id}', 'controllers.vote:voteAction')
	->bind('vote');

// ADMIN AJAX
$app->get('/user/vote_datatables/{username}', 'controllers.user:votesUserDatatablesAction')
	->bind('vote_datatables');

$app->get('/user/comment_datatables/{username}', 'controllers.user:commentsUserDatatablesAction')
	->bind('comment_datatables');
	
// USER
$app->get('/user/login', 'controllers.user:connect')
	->bind('login');

$app->get('/user/list', 'controllers.user:listAction')
	->bind('list');

$app->get('/user/show/{username}', 'controllers.user:showAction')
	->value('username', false)
	->bind('user_show');

$app->get('/user/new', 'controllers.user:newAction')
	->bind('user_new');

$app->post('/user/create', 'controllers.user:createAction')
	->bind('user_create');

$app->get('/user/edit/{id}', 'controllers.user:editAction')
	->value('id', false)
	->bind('user_edit');

$app->post('/user/update/{id}', 'controllers.user:updateAction')
	->value('id', false)
	->bind('user_update');

$app->get('/user/updatepassword', 'controllers.user:updatePasswordAction')
	->bind('user_udpatepassword');

$app->post('/user/updatepasswordsave', 'controllers.user:updatePasswordSaveAction')
	->bind('user_updatepasswordsave');

$app->get('/user/forgottenpassword', 'controllers.user:forgottenPasswordAction')
	->bind('user_forgottenpassword');

$app->post('/user/forgottenpasswordsend', 'controllers.user:forgottenPasswordSendAction')
	->bind('user_forgottenpasswordsend');

// ADMIN COUNTRY
$app->get('/admin/country/index', 'controllers.countryadmin:indexAction')
    ->bind('countryadmin_index');

$app->get('/admin/country/indexdatatables', 'controllers.countryadmin:indexDatatablesAction')
    ->bind('countryadmin_indexdatatables');

$app->get('/admin/country/new', 'controllers.countryadmin:newAction')
    ->bind('countryadmin_new');

$app->post('/admin/country/create', 'controllers.countryadmin:createAction')
    ->bind('countryadmin_create');

$app->get('/admin/country/show/{id}', 'controllers.countryadmin:showAction')
    ->bind('countryadmin_show');

$app->get('/admin/country/edit/{id}', 'controllers.countryadmin:editAction')
    ->bind('countryadmin_edit');

$app->post('/admin/country/upate/{id}', 'controllers.countryadmin:updateAction')
    ->bind('countryadmin_update');

// ADMIN TAG
$app->get('/admin/tag/index', 'controllers.tagadmin:indexAction')
    ->bind('tagadmin_index');

$app->get('/admin/tag/indexdatatables', 'controllers.tagadmin:indexDatatablesAction')
    ->bind('tagadmin_indexdatatables');

$app->get('/admin/tag/new', 'controllers.tagadmin:newAction')
    ->bind('tagadmin_new');

$app->post('/admin/tag/create', 'controllers.tagadmin:createAction')
    ->bind('tagadmin_create');

$app->get('/admin/tag/show/{id}', 'controllers.tagadmin:showAction')
    ->bind('tagadmin_show');

$app->get('/admin/tag/edit/{id}', 'controllers.tagadmin:editAction')
    ->bind('tagadmin_edit');

$app->post('/admin/tag/upate/{id}', 'controllers.tagadmin:updateAction')
    ->bind('tagadmin_update');

// ADMIN PROVERB
$app->get('/admin/proverb/index', 'controllers.proverbadmin:indexAction')
    ->bind('proverbadmin_index');

$app->get('/admin/proverb/indexdatatables', 'controllers.proverbadmin:indexDatatablesAction')
    ->bind('proverbadmin_indexdatatables');

$app->get('/admin/proverb/new', 'controllers.proverbadmin:newAction')
    ->bind('proverbadmin_new');

$app->post('/admin/proverb/create', 'controllers.proverbadmin:createAction')
    ->bind('proverbadmin_create');

$app->get('/admin/proverb/show/{id}', 'controllers.proverbadmin:showAction')
    ->bind('proverbadmin_show');

$app->get('/admin/proverb/edit/{id}', 'controllers.proverbadmin:editAction')
    ->bind('proverbadmin_edit');

$app->post('/admin/proverb/upate/{id}', 'controllers.proverbadmin:updateAction')
    ->bind('proverbadmin_update');

$app->get('/admin/proverb/delete/{id}', 'controllers.proverbadmin:deleteAction')
    ->bind('proverbadmin_delete');

$app->get('/admin/proverb/newFastMultiple', 'controllers.proverbadmin:newFastMultipleAction')
    ->bind('proverbadmin_newfastmultiple');

$app->post('/admin/proverb/addFastMultiple', 'controllers.proverbadmin:addFastMultipleAction')
    ->bind('proverbadmin_addfastmultiple');

// ADMIN PAGE
$app->get('/admin/page/index', 'controllers.pageadmin:indexAction')
    ->bind('pageadmin_index');

$app->get('/admin/page/indexdatatables', 'controllers.pageadmin:indexDatatablesAction')
    ->bind('pageadmin_indexdatatables');

$app->get('/admin/page/new', 'controllers.pageadmin:newAction')
    ->bind('pageadmin_new');

$app->post('/admin/page/create', 'controllers.pageadmin:createAction')
    ->bind('pageadmin_create');

$app->get('/admin/page/show/{id}', 'controllers.pageadmin:showAction')
    ->bind('pageadmin_show');

$app->get('/admin/page/edit/{id}', 'controllers.pageadmin:editAction')
    ->bind('pageadmin_edit');

$app->post('/admin/page/upate/{id}', 'controllers.pageadmin:updateAction')
    ->bind('pageadmin_update');

$app->post('/admin/page/upload_image_mce', 'controllers.pageadmin:uploadImageMCEAction')
	->bind('pageadmin_upload_image_mce');

// ADMIN VERSION
$app->get('/admin/version/index', 'controllers.versionadmin:indexAction')
    ->bind('versionadmin_index');

$app->get('/admin/version/indexdatatables', 'controllers.versionadmin:indexDatatablesAction')
    ->bind('versionadmin_indexdatatables');

$app->get('/admin/version/new', 'controllers.versionadmin:newAction')
    ->bind('versionadmin_new');

$app->post('/admin/version/create', 'controllers.versionadmin:createAction')
    ->bind('versionadmin_create');

$app->get('/admin/version/show/{id}', 'controllers.versionadmin:showAction')
    ->bind('versionadmin_show');

$app->get('/admin/version/edit/{id}', 'controllers.versionadmin:editAction')
    ->bind('versionadmin_edit');

$app->post('/admin/version/upate/{id}', 'controllers.collectionadmin:updateAction')
    ->bind('versionadmin_update');

// ADMIN CONTACT FORM
$app->get('/admin/contact/index', 'controllers.contactadmin:indexAction')
    ->bind('contactadmin_index');

$app->get('/admin/contact/indexdatatables', 'controllers.contactadmin:indexDatatablesAction')
    ->bind('contactadmin_indexdatatables');

$app->get('/admin/contact/show/{id}', 'controllers.contactadmin:showAction')
    ->bind('contactadmin_show');
	
// ADMIN USER
$app->get('/admin/user/index', 'controllers.useradmin:indexAction')
    ->bind('useradmin_index');

$app->get('/admin/user/indexdatatables', 'controllers.useradmin:indexDatatablesAction')
    ->bind('useradmin_indexdatatables');

$app->get('/admin/user/show/{id}', 'controllers.useradmin:showAction')
    ->bind('useradmin_show');

$app->get('/admin/user/enabled/{id}/{state}', 'controllers.useradmin:enabledAction')
    ->bind('useradmin_enabled');