<?php
class ReprocessarCommand extends ConsoleCommand
{

    public function run($args)
    {
        $modelPhoneBook = PhoneBook::model()->findAll('reprocessar > 0 AND status =1')

        foreach ($modelPhoneBook as $key => $phonebook) {
            sleep(1);
            $modelPhoneNumberCount = PhoneNumber::model()->count('id_phonebook = :key AND id_category = 1', array(':key' => $phonebook->id));

            if ($modelPhoneNumberCount < 100) {
                $phonebook->reprocessar -= $phonebook->reprocessar;
                $phonebook->save();

                PhoneNumber::model()->updateAll(
                    array('status' => 1, 'id_category' => 1, 'id_user' = null),
                    'id_phonebook = :key',
                    array(':key' => $phonebook->id));
            }
        }
    }
}
