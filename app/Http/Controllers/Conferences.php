<?php

namespace App\Http\Controllers;

use App\Conference;
use BaoPham\DynamoDb\Facades\DynamoDb;
use Illuminate\Http\Request;

class Conferences extends Controller
{
    public function index()
    {
        $list = Conference::all();
        return view('conference', ['list' => $list]);
    }

    public function add(Request $request)
    {
        $conference = new Conference();
        $conference->setAttribute('email', $request->get('email'));
        $conference->setAttribute('members', []);
        $conference->save();
        $this->addToDynamoDb($conference);
        $this->addToTransportFile($conference);

        return redirect('/conference');
    }

    public function addMember(Request $request)
    {
        $conference = Conference::find($request->get('conference_id'));
        $members = $conference->members;
        $members[] = $request->get('email');
        $conference->setAttribute('members', $members);
        $conference->save();
        $this->addToDynamoDb($conference);

        return redirect('/conference');
    }

    public function removeMember(Request $request)
    {
        $conference = Conference::find($request->get('conference_id'));
        $members = $conference->members;
        $members = array_diff($members, [$request->get('email')]);
        $conference->setAttribute('members', $members);
        $conference->save();
        $this->addToDynamoDb($conference);

        return redirect('/conference');
    }

    public function delete(Request $request)
    {
        $conference = Conference::find($request->get('conference_id'));
        $this->deleteFromDynamoDb($conference);
        $this->removeFromTransportFile($conference);
        $conference->delete();

        return redirect('/conference');
    }

    private function addToDynamoDb(Conference $conference): void
    {
        $this->deleteFromDynamoDb($conference);

        DynamoDb::table('EmailRecipientMapping')
            ->setItem(DynamoDb::marshalItem([
                'email' => $conference->email,
                'recipients' => $conference->members
            ]))
            ->prepare()->putItem();
    }

    private function deleteFromDynamoDb(Conference $conference): void
    {
        DynamoDb::table('EmailRecipientMapping')
            ->setKey(DynamoDb::marshalItem(['email' => $conference->email]))
            ->prepare()->deleteItem();
    }

    private function addToTransportFile(Conference $conference): void
    {
        $filePath = '/etc/postfix/transport';
        $newLine = sprintf("%s\tsmtp:[inbound-smtp.eu-central-1.amazonaws.com]:25\n", $conference->email);

        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);

            // Add the new line
            $content .= $newLine;

            // Write the updated content back to the file
            file_put_contents($filePath, $content);

            exec('postmap /etc/postfix/transport');
            exec('systemctl reload postfix');
        }
    }

    private function removeFromTransportFile(Conference $conference): void
    {
        $filePath = '/etc/postfix/transport';
        if (file_exists($filePath)) {
            $content = file($filePath, FILE_IGNORE_NEW_LINES); // Read the file into an array

            // Filter out the line containing 'conf@pinkevicius.lt'
            $content = array_filter($content, function($line) use ($conference) {
                return strpos($line, $conference->email) === false;
            });

            // Write the updated content back to the file
            file_put_contents($filePath, implode("\n", $content) . "\n");

            exec('postmap /etc/postfix/transport');
            exec('systemctl reload postfix');
        }
    }
}
