<?php

namespace Foodsharing\RestApi\Models\User;

use Foodsharing\RestApi\Models\User\CoordinatesModel;
use Foodsharing\RestApi\Models\User\UserPermissionsModel;
use Foodsharing\RestApi\Models\User\UserStatisticsModel;

use OpenApi\Annotations as OA;

/**
 * Desribes foodsharing user and this information
 *
 * @OA\Schema(required={"id", "loggedIn", "foodsaver", "isVerified"})
 */
class UserDetailsModel
{
	/** 
	 * The foodsharer identifier
	 *
	 * @OA\Property(format="int64", example=1)
	 */
	public int $id;

	/**
	 * UNCLEAR USAGE: API caller is logged in.
	 */
    public bool $loggedIn;

	/**
	 * UNCLEAR USAGE: API caller role in system  is the role foodsaver ("fs").
	 *
	 * @OA\Property(type="Boolean", example=true)
	 */
	public string $foodsaver;

	/**
	 * The foodsaver is verified by a ambassador to a foodsharer. The status of verification is related to the home region.
	 * If the user change his home region then the verification status is lost.
	 *
	 * @OA\Property(type="Boolean", example=true)
	 */
	public bool $isVerified;

	/**
	 * Home region id of the user. The user have only one home region which.
	 *
	 * @OA\Property(type="int", example=1)
	 */
	public ?int $regionId = null;

	/**
	 * Home region name of the user. The user have only a home region of the foodsharing quiz is finished successful.
	 *
	 * @OA\Property(type="int", example="Hamburg")
	 */
    public ?string $regionName = "";

	/**
	 * NEW: The user have finished the quiz to choice a home region.
	 *
	 * @OA\Property(type="Boolean", example=true)
	 */
	public bool $isRegionQuizDone;
	
	/**
	 *
	 * @OA\Property(type="string", example=true)
	 */
    public string $aboutMePublic = "";

	/**
	 *
	 * @OA\Property(type="string", example=true)
	 */
    public ?string $mailboxId = "";

	/**
	 *
	 * @OA\Property(type="string", example=true)
	 */
	public ?string $hasCalendarToken = "";

	/**
	 *
	 * @OA\Property(type="string", example=true)
	 */
	public ?string $firstname = "";

	/**
	 *
	 * @OA\Property(type="string", example=true)
	 */
	public ?string $lastname = "";
	
	/**
	 *
	 * @OA\Property(type="string", example=true)
	 */
	public ?string $gender = "";
	
	/**
	 *
	 * @OA\Property(type="string", example=true)
	 */
	public ?string $photo = "";

	/**
	 *
	 * @OA\Property(type="string", example=true)
	 */
	public ?string $sleeping = "";

	/**
	 *
	 * @OA\Property(type="string", example=true)
	 */
	public ?string $address = "";

	/**
	 *
	 * @OA\Property(type="string", example=true)
	 */
	public ?string $postcode = "";

	/**
	 *
	 * @OA\Property(type="string", example=true)
	 */
	public ?string $email = "";

	/**
	 *
	 * @OA\Property(type="string", example=true)
	 */
	public ?string $landline = ""; // Telefon

	/**
	 *
	 * @OA\Property(type="string", example=true)
	 */
	public ?string $mobile = "";

	/**
	 *
	 * @OA\Property(type="string", example=true)
	 */
	public ?string $birthday = "";

	/**
	 *
	 * @OA\Property(type="string", example=true)
	 */
	public ?CoordinatesModel $coordinates = null;
    

	/**
	 *
	 * @OA\Property(type="string", example=true)
	 */
	public ?string $role = "";
 
	/**
	 *
	 * @OA\Property(type="string", example=true)
	 */
	public ?string $position = "";


	/**
	 *
	 * @OA\Property(type="string", example=true)
	 */
	public ?UserStatisticsModel $stats = null;

	/**
	 *
	 * @OA\Property(type="string", example=true)
	 */
	public ?UserPermissionsModel $permissions = null;
}



/*
$loggedIn = $this->session->may();
		$mayEditUserProfile = $this->profilePermissions->mayEditUserProfile($data['id']);
		$mayAdministrateUserProfile = $this->profilePermissions->mayAdministrateUserProfile($data['id'], $data['bezirk_id']);

		$response = [];
		$response['id'] = $data['id'];
		$response['foodsaver'] = ($this->session->may('fs')) ? true : false;
		$response['isVerified'] = ($data['verified'] === 1) ? true : false;
		$response['regionId'] = $data['bezirk_id'];
		$response['regionName'] = ($data['bezirk_id'] === null) ? null : $this->regionGateway->getRegionName($data['bezirk_id']);
		$response['aboutMePublic'] = $data['about_me_public'];

		if ($loggedIn) {
			$infos = $this->foodsaverGateway->getFoodsaverBasics($this->session->id());

			$response['mailboxId'] = $data['mailbox_id'];
			$response['hasCalendarToken'] = $this->settingsGateway->getApiToken($this->session->id());
			$response['firstname'] = $data['name'];
			$response['lastname'] = $data['nachname'];
			$response['gender'] = $data['geschlecht'];
			$response['photo'] = $data['photo'];
			$response['sleeping'] = boolval($data['sleep_status']);
			$response['homepage'] = $data['homepage'];

			$response['stats']['weight'] = floatval($infos['stat_fetchweight']);
			$response['stats']['count'] = $infos['stat_fetchcount'];

			$response['permissions'] = [
				'mayEditUserProfile' => $mayEditUserProfile,
				'mayAdministrateUserProfile' => $mayAdministrateUserProfile,
				'administrateBlog' => $this->blogPermissions->mayAdministrateBlog(),
				'editQuiz' => $this->quizPermissions->mayEditQuiz(),
				'handleReports' => $this->reportPermissions->mayHandleReports(),
				'addStore' => $this->storePermissions->mayCreateStore(),
				'manageMailboxes' => $this->mailboxPermissions->mayManageMailboxes(),
				'editContent' => $this->contentPermissions->mayEditContent(),
				'administrateNewsletterEmail' => $this->newsletterEmailPermissions->mayAdministrateNewsletterEmail(),
				'administrateRegions' => $this->regionPermissions->mayAdministrateRegions(),
			];
		} else {
			$response['firstname'] = ($data['name'] === null) ? null : $data['name'][0]; // Only return first character
		}

		if ($mayEditUserProfile) {
			$response['coordinates'] = [
				'lat' => floatval($data['lat']),
				'lon' => floatval($data['lon'])
			];
			$response['address'] = $data['anschrift'];
			$response['city'] = $data['stadt'];
			$response['postcode'] = $data['plz'];
			$response['email'] = $data['email'];
			$response['landline'] = $data['telefon'];
			$response['mobile'] = $data['handy'];
			$response['birthday'] = $data['geb_datum'];
			$response['aboutMeIntern'] = $data['about_me_intern'];
		}

		if ($mayAdministrateUserProfile) {
			$response['role'] = $data['rolle'];
			$response['position'] = $data['position'];
		}
        */