<?php

use BaoPham\DynamoDb\Facades\DynamoDb;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/conference', 'Conferences@index');

Route::post('/conference/add', 'Conferences@add')->name('conference.add');

Route::post('/conference/addmember', 'Conferences@addMember')->name('conference.addmember');

Route::post('/conference/removemember', 'Conferences@removeMember')->name('conference.removemember');

Route::post('/conference/delete', 'Conferences@delete')->name('conference.delete');

// Insert to dynamodb and remove from dynamodb
Route::get('/dynamodb', function() {
    DynamoDb::table('EmailRecipientMapping')
        ->setItem(DynamoDb::marshalItem(['email' => 'a@b.com', 'recipients' => ['edvinas@uola.lt', 'info@uola.lt']]))
        ->prepare()->putItem();
    $result = DynamoDb::table('EmailRecipientMapping')->prepare()->scan();
    dd($result);
});

// Delete item from dynamodb
Route::get('/dynamodb2', function() {
    $result = DynamoDb::table('EmailRecipientMapping')
//        ->setKey(DynamoDb::marshalItem(['email' => 'a@b.com']))
            ->setFilterExpression('email = :email')
        ->setExpressionAttributeValues([':email' => ['S' => 'a@bd.com']])
        ->prepare()->scan();
    dd($result);
});

Route::get('/dynamodb3', function() {
    $result = DynamoDb::table('EmailRecipientMapping')
        ->setKey(DynamoDb::marshalItem(['email' => 'a@b.com']))
        ->setUpdateExpression('SET recipients[1] = #recipients')
        ->setExpressionAttributeValues(['#recipients' => ['S' => 'edvinas@relsta.lt']])
        ->prepare()->updateItem();
    dd($result);
});

Route::get('/file', function(){
    $filePath = '/etc/postfix/transport';

    dd(File::exists($filePath));
});
