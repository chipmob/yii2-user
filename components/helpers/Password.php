<?php

namespace chipmob\user\components\helpers;

use Yii;

/**
 * Password helper.
 */
class Password
{
    public static function hash(string $password): string
    {
        return Yii::$app->security->generatePasswordHash($password, Yii::$app->getModule('user')->cost);
    }

    public static function validate(string $password, string $hash): bool
    {
        return Yii::$app->security->validatePassword($password, $hash);
    }

    public static function generate(int $length): string
    {
        $sets = [
            'abcdefghjkmnpqrstuvwxyz',
            'ABCDEFGHJKMNPQRSTUVWXYZ',
            '23456789',
        ];
        $all = '';
        $password = '';
        foreach ($sets as $set) {
            $password .= $set[array_rand(str_split($set))];
            $all .= $set;
        }

        $all = str_split($all);
        for ($i = 0; $i < $length - count($sets); $i++) {
            $password .= $all[array_rand($all)];
        }

        $password = str_shuffle($password);

        return $password;
    }
}
