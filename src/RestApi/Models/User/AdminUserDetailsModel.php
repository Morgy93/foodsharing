<?php

namespace Foodsharing\RestApi\Models\User;

use OpenApi\Annotations as OA;

/**
 * Describe user information about user which are ambassador and higher.
 *
 * @OA\Schema()
 */
class AdminUserDetailsModel extends EditableUserModel
{
	/**
	 *  Role of the user in the foodsharing system.
	 *
	 *  - 0: FOODSHARER
	 *  - 1: FOODSAVER
	 *  - 2: STORE_MANAGER
	 *  - 3: AMBASSADOR
	 *  - 4: ORGA
	 *  - 5: SITE_ADMIN
	 *
	 * @OA\Property(type="int", example=1, enum={0, 1, 2, 3, 4, 5})
	 */
	public ?int $role = 0;

	/**
	 * UNCLEAR Meaning of the field waht is the difference between role and position?
	 *
	 * @OA\Property(type="string", example="", maxLength=255)
	 */
	public ?string $position = '';
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
