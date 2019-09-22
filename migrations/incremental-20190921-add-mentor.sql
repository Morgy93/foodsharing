alter table fs_betrieb
    add allow_tutoring tinyint(3) default 0 null after presse;

alter table fs_betrieb_team
    add tutor tinyint(3) default 0 null after verantwortlich;
