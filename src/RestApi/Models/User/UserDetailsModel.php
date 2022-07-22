<?php

namespace Foodsharing\RestApi\Models\User;

use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

/**
 * Desribes foodsharing user and this information.
 *
 * @OA\Schema(required={"id", "loggedIn", "foodsaver", "isVerified"})
 */
class UserDetailsModel
{
	/**
	 * The foodsharer identifier of user.
	 *
	 * @OA\Property(format="int32", example=1)
	 */
	public int $id;

	/**
	 * UNCLEAR USAGE: API caller is logged in.
	 *
	 * @OA\Property(type="Boolean", example=true)
	 */
	public bool $loggedIn;

	/**
	 * UNCLEAR USAGE: API caller role in system  is the role foodsaver ("fs").
	 *
	 * @OA\Property(type="Boolean", example=true)
	 */
	public bool $foodsaver;

	/**
	 * The user is verified by a ambassador to a foodsharer. The status of verification is related to the home region.
	 * If the user change the home region then the verification status is lost.
	 *
	 * @OA\Property(type="Boolean", example=true)
	 */
	public bool $isVerified;

	/**
	 * Home region id of the user. The user have only one home region which.
	 *
	 * @OA\Property(type="int32", example=1)
	 */
	public ?int $regionId = null;

	/**
	 * Home region name of the user. The user have only a home region of the foodsharing quiz is finished successful.
	 *
	 * @OA\Property(type="string", example="Hamburg", maxLength=120)
	 */
	public ?string $regionName = '';

	/**
	 * NEW: The user have finished the quiz to choice a home region.
	 *
	 * @OA\Property(type="Boolean", example=true)
	 */
	public bool $isRegionQuizDone;

	/**
	 * Public text to describe the user by it self.
	 *
	 * @OA\Property(type="string", example=true, maxLength=16777215)
	 */
	public string $aboutMePublic = '';

	/**
	 * Foodsharing internal text to describe the user by it self.
	 *
	 * @OA\Property(type="string", example="", maxLength=16777215)
	 */
	public string $aboutMeIntern = '';

	/**
	 * First name of the user.
	 *
	 * @OA\Property(type="string", example=true, maxLength=120)
	 */
	public ?string $firstname = '';

	/**
	 * Last name of the user.
	 *
	 * @OA\Property(type="string", example=true, maxLength=120)
	 */
	public ?string $lastname = '';

	/**
	 * Gender of the user.
	 *
	 * 	- 0: NOT_SELECTED
	 * 	- 1: MALE
	 * 	- 2: FEMALE
	 *  - 3: DIVERSE
	 *
	 * @OA\Property(type="int", enum={0, 1, 2, 3})
	 */
	public int $gender = 0;

	/**
	 * @OA\Property(type="string", example=true)
	 */
	public ?string $mailboxId = '';

	/**
	 * @OA\Property(type="string", example=true)
	 */
	public ?string $hasCalendarToken = '';

	/**
	 * Path to user picture.
	 *
	 * @OA\Property(type="string", format="uri",example="/upload/d87ce740-0985-11ed-861d-0242ac120002", maxLength=50)
	 */
	public ?string $photo = '';

	/**
	 * @OA\Property(type="string", example=true)
	 */
	public ?string $sleeping = '';

	/**
	 * Living address of user.
	 *
	 * @OA\Property(type="string", example=true, maxLength=120)
	 */
	public ?string $address = '';

	/**
	 * Living city of user.
	 *
	 * @OA\Property(type="string", example=true, maxLength=100)
	 */
	public ?string $city = '';

	/**
	 * Living address zip code of user.
	 *
	 * @OA\Property(type="string", example="69123", maxLength=10)
	 */
	public ?string $postcode = '';

	/**
	 * E-Mail address of the user which is used for user account verification ewsletter subscribtions.
	 *
	 * @OA\Property(type="email", example="no-response@foodsharing.de", maxLength=120)
	 */
	public ?string $email = '';

	/**
	 * Contact number to call user.
	 *
	 * @OA\Property(type="string", example="+49 30 123456789", maxLength=50)
	 */
	public ?string $landline = ''; // Telefon

	/**
	 * Mobile contact number to call user.
	 *
	 * @OA\Property(type="string", example="+49 179 12345678", maxLength=50)
	 */
	public ?string $mobile = '';

	/**
	 * Birthday of the user.
	 *
	 * @OA\Property(type="string", format="date", example="1983-04-15")
	 */
	public ?string $birthday = '';

	/**
	 * @Model(type=CoordinatesModel::class)
	 */
	public ?CoordinatesModel $coordinates = null;

	/**
	 * @OA\Property(type="string", example=true)
	 */
	public ?string $role = '';

	/**
	 * @OA\Property(type="string", example=true)
	 */
	public ?string $position = '';

	/**
	 * @Model(type=UserStatisticsModel::class)
	 */
	public ?UserStatisticsModel $stats = null;

	/**
	 * @Model(type=UserPermissionsModel::class)
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
