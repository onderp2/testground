<?php

declare(strict_types=1);

namespace App\Service;
class EditUserService
{
    public function editUserProfile($data)
    {
        // Редактирование профиля организации заказчика
        if (isset($data['client_profile_id']) && $this->getUserType() == Model_User::TYPE_USER) {
            // Запретить заказчику самостоятельную смену профиля
            if (getActiveUser() == $this->getId() && !empty(
                $this->getClientProfileId()
                ) && $data['client_profile_id'] != $this->getClientProfileId()) {
                throw new ResponseException('Нельзя сменить тип организации', 405);
            }

            // Полное наименование организации для всех типов компании
            if ($data['client_profile_id'] != 3 && empty($data['cmp_full_name'])) {
                throw new ResponseException('Не заполнено поле полное наименование', 405);
            }

            // только для не юр лиц
            if ($data['client_profile_id'] != 1 && empty($data['cmp_short_name'])) {
                $data['cmp_short_name'] = $data['cmp_full_name'];
            }

            // Полное наименование организации только для физлиц
            if ($data['client_profile_id'] == 3 && empty($this->getCmpFullName()) && empty($data['cmp_full_name'])) {
                $data['cmp_full_name'] = empty($data['last_name']) ?: $data['last_name'];
                $data['cmp_full_name'] .= empty($data['first_name']) ?: " " . $data['first_name'];
                $data['cmp_full_name'] .= empty($data['middle_name']) ?: " " . $data['middle_name'];
            }

            // Поле телефона обязательно для заполнения
            if (empty($data['phone'])) {
                // ЗАПОЛНЯЕТСЯ ПРИ РЕГИСТРАЦИИ
                //throw new ResponseException('Не заполнено поле телефона', 405);
            }

            // Банковские реквизиты обязательны для всех типов компаний
            // для ФИЗЛИЦ счет не обязателен
            if ($data['client_profile_id'] != 3 || !empty($data['bank_accounts'])) {
                if (empty($data['quick_registration']) || $data['quick_registration'] !== true) {
                    if (empty($data['bank_accounts']['account'])
                        || empty($data['bank_accounts']['bik'])
                        || empty($data['bank_accounts']['bank'])
                        || empty($data['bank_accounts']['bank_addr'])
                    ) {
                        throw new ResponseException(
                            'Не заполнено одно или несколько обязательных полей банковских реквизитов', 405
                        );
                    }
                    $_data = $data['bank_accounts'];
                    $_data['owner_id'] = $data['id'];
                    $_data['owner_type'] = Model_BankAccount::OWNER_TYPE_USER;
                    $_data['actual'] = true;

                    if (isset($data['bank_accounts']['id']) && $data['bank_accounts']['id']) {
                        $bankdata = Model_BankAccount::load($data['bank_accounts']['id']);
                        if ($bankdata) {
                            $bankdata->update($_data);
                        } else {
                            throw new ResponseException(
                                'Не заполнено одно или несколько обязательных полей банковских реквизитов', 405
                            );
                        }
                    } else {
                        Model_BankAccount::create($_data, $_data['owner_id'], $_data['owner_type']);
                    }
                    unset($data['bank_accounts']);
                }
            }
            // Прочее
            $require_inn = false;
            $require_kpp = false;
            $require_ogrn = false;
            $require_address = false;
            $require_legal_address = false;
            $require_postal_address = false;
            if ($data['client_profile_id'] == 1) {
                // ЮЛ
                $require_inn = true;
                $require_ogrn = true;
                $require_legal_address = true;
                $require_postal_address = true;
            } elseif ($data['client_profile_id'] == 2) {
                // ИП
                $require_inn = true;
                $require_kpp = false;
                $require_ogrn = true;
                $require_legal_address = false;
                $require_postal_address = false;
            } elseif ($data['client_profile_id'] == 3) {
                // ФЛ
                $require_legal_address = false;
                $require_postal_address = false;
                if (strlen($data['inn']) != 12) {
                    throw new ResponseException('Неверно заполнено поле ИНН', 405);
                }
            } else {
                throw new ResponseException('Указан невереный тип организации');
            }

            // ИНН, КПП, ОГРН
            if ($require_inn && empty($data['inn'])) {
                throw new ResponseException('Не заполнено поле ИНН');
            } else {
                if ($require_inn) {
                    parent::_validateStatic($data['inn'], 'inn', array('inn'));
                }
            }
            if ($require_kpp && empty($data['kpp'])) {
                throw new ResponseException('Не заполнено поле КПП');
            }
            if ($require_ogrn && empty($data['ogrn'])) {
                throw new ResponseException('Не заполнено поле ОГРН');
            }

            if ($require_postal_address) {
                // Почтовый адрес
                if (empty($data['postal_address']['index'])
                    || empty($data['postal_address']['region'])
                    || empty($data['postal_address']['city'])
                    || empty($data['postal_address']['street'])
                    || (empty($data['postal_address']['house']) && empty($data['postal_address']['house_unit']))
                ) {
                    throw new ResponseException('Не заполнены обязательные поля почтового адреса');
                }

                if (mb_strlen($data['postal_address']['house_unit'], "UTF-8") > 20) {
                    $pseudo = Model_Address::_parameters()['house_unit']['pseudo'];
                    throw new ResponseException(
                        "Поле '$pseudo' 'Почтового адреса' может иметь длину не более 20 символов"
                    );
                }
                if (mb_strlen($data['postal_address']['housing_unit'], "UTF-8") > 20) {
                    $pseudo = Model_Address::_parameters()['housing_unit']['pseudo'];
                    throw new ResponseException(
                        "Поле '$pseudo' 'Почтового адреса' может иметь длину не более 20 символов"
                    );
                }
                if (mb_strlen($data['postal_address']['office_unit'], "UTF-8") > 20) {
                    $pseudo = Model_Address::_parameters()['office_unit']['pseudo'];
                    throw new ResponseException(
                        "Поле '$pseudo' 'Почтового адреса' может иметь длину не более 20 символов"
                    );
                }

                if (isset($data['postal_address']['id'])) {
                    unset($data['postal_address']['id']);
                }
                if (empty($data['postal_address']['country_iso_nr'])) {
                    $data['postal_address']['country_iso_nr'] = 643;
                }
                $_data = $data['postal_address'];
                $_data['owner_id'] = $data['id'];
                $_data['owner_type'] = 'user';
                $_data['actual'] = true;
                Model_Address::saveAddress($_data, Model_Address::TYPE_POSTAL);
                unset($data['postal_address']);
            }

            if ($require_legal_address) {
                // Юридический адрес
                if (empty($data['legal_address']['index'])
                    || empty($data['legal_address']['region'])
                    || empty($data['legal_address']['city'])
                    || empty($data['legal_address']['street'])
                    || (empty($data['legal_address']['house']) && empty($data['legal_address']['house_unit']))
                ) {
                    throw new ResponseException('Не заполнены обязательные поля юридического адреса');
                }

                if (mb_strlen($data['legal_address']['house_unit'], "UTF-8") > 20) {
                    $pseudo = Model_Address::_parameters()['house_unit']['pseudo'];
                    throw new ResponseException(
                        "Поле '$pseudo' 'Юридического адреса' может иметь длину не более 20 символов"
                    );
                }
                if (mb_strlen($data['legal_address']['housing_unit'], "UTF-8") > 20) {
                    $pseudo = Model_Address::_parameters()['housing_unit']['pseudo'];
                    throw new ResponseException(
                        "Поле '$pseudo' 'Юридического адреса' может иметь длину не более 20 символов"
                    );
                }
                if (mb_strlen($data['legal_address']['office_unit'], "UTF-8") > 20) {
                    $pseudo = Model_Address::_parameters()['office_unit']['pseudo'];
                    throw new ResponseException(
                        "Поле '$pseudo' 'Юридического адреса' может иметь длину не более 20 символов"
                    );
                }

                if (isset($data['legal_address']['id'])) {
                    unset($data['legal_address']['id']);
                }
                if (empty($data['legal_address']['country_iso_nr'])) {
                    $data['legal_address']['country_iso_nr'] = 643;
                }
                $_data = $data['legal_address'];
                $_data['owner_id'] = $data['id'];
                $_data['owner_type'] = 'user';
                $_data['actual'] = true;
                Model_Address::saveAddress($_data, Model_Address::TYPE_LEGAL);
                unset($data['legal_address']);
            }
        }

        // изменение user_email существующего пользователя
        if (!empty($data['user_email'])) {
            // Проверим уникальность
            if ($this->getUserEmail() != $data['user_email']) {
                // Проверка дубликатов логина и почты
                if ($this->isEmailExists($data['user_email'])) {
                    throw new ResponseException(
                        'Указанный адрес электронной почты <b>' . $data['user_email'] . '</b> уже используется в другом личном кабинете. Вам необходимо указать адрес электронной почты который ни разу не был задействован в системе Онлайн заказа услуг',
                        405
                    );
                }

                $data['username'] = $data['user_email'];
            }
        }

        if (isset($data['role'])) {
            $data['user_type'] = intval($data['user_type']);
            if (!isset($data['user_type'])) {
                throw new ResponseException('Не указан тип пользователя', 405);
            }

            self::checkTypeRoleRequires($data);// вынес проверки в общее место

            $this->setUserRoles(array(Model_User::USER_ROLE_AUTHORIZED, intval($data['role'])));

            // Очистка лишней информации
            if ($data['user_type'] == Model_User::TYPE_PARTNER) {
                unset($data['point_id']);
            } else {
                if ($data['user_type'] == Model_User::TYPE_CALLSPEC) {
                    unset($data['partner_id'], $data['point_id']);
                } else {
                    if ($data['user_type'] == Model_User::TYPE_OPERATOR) {
                        unset($data['partner_id']);
                    } else {
                        throw new ResponseException('Указан неизвестный тип пользователя');
                    }
                }
            }
        } else {
            // Если не были переданы в паре роль и тип пользователя, то стираем и роль и тип
            if (isset($data['user_type'])) {
                unset($data['user_type']);
            }
        }

        $this->update($data, false, array());
    }
}
