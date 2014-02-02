<?php namespace Kareem3d\Membership;

use Kareem3d\Eloquent\Model;

class UserContact extends Model {

    const EMAIL = 'email';
    const MOBILE = 'mobile';

    /**
     * @var string
     */
    protected $table = 'ka_user_contacts';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @param UserInfo $userInfo
     * @param $value
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function insertEmail(UserInfo $userInfo, $value)
    {
        return static::insertType($userInfo, static::EMAIL, $value);
    }

    /**
     * @param UserInfo $userInfo
     * @param $value
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function insertMobile(UserInfo $userInfo, $value)
    {
        return static::insertType($userInfo, static::MOBILE, $value);
    }

    /**
     * @param UserInfo $userInfo
     * @param $type
     * @param $value
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function insertType(UserInfo $userInfo, $type, $value)
    {
        return $userInfo->contacts()->create(array(
            'type' => $type,
            'value' => $value,
        ));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function userInfo()
    {
        return $this->belongsTo(UserInfo::getClass());
    }

}