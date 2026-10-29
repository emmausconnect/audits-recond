curl -L -o C:\Windows\Panther\unattend.xml https://audits.emmaus-connect.org/storage/ventoy/win_unattended_installfromusb.xml
%WINDIR%\System32\Sysprep\Sysprep.exe /oobe /unattend:C:\Windows\Panther\unattend.xml /reboot
