alter table fs_betrieb
    add allow_mentor tinyint(3) default 0 null after presse;

alter table fs_betrieb_team
    add mentor tinyint(3) default 0 null after verantwortlich;
