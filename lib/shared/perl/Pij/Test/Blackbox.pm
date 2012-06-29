package Pij::Test::Blackbox;
use strict;
use base 'Exporter';

our @EXPORT = qw( base_uri db_domain selenium_rc_host );

my %domains = (
    'pijdev01.mpr.org'  => 'dev',
    'pijdev02.mpr.org'  => 'dev',
    'pijdev03.mpr.org'  => 'dev',
    'pijdev04.mpr.org'  => 'dev',
    'pijtest01.mpr.org' => 'dev',
    'pijqa01.mpr.org'   => 'dev',
    'pijnat01.mpr.org'  => 'prod'
);
my $selenium_host = '10.2.9.129';
my %servers       = (
    'formbuilder.pijdev01.mpr.org' =>
        'pijdev01.publicradio.org/formbuilder/internal',
    'formbuilder.pijdev02.mpr.org'  => 'pijdev02.publicradio.org/fb/internal',
    'formbuilder.pijtest01.mpr.org' => 'pijqa01.publicradio.org/fb',
    'formbuilder.pijqa01.mpr.org'   => 'pijqa01.publicradio.org/fb',
    'formbuilder.pijnat01.mpr.org'  => 'www.publicinsightnetwork.org'
);

sub base_uri {

    # env overrides
    if ( exists $ENV{FB_BASE_URL} ) {
        return $ENV{FB_BASE_URL};
    }

    # get the app the user wants the base uri for
    my $app = shift;

    # the hostname of the current machine is used to determine the base uri as
    # well
    my $hn = `hostname`;
    chomp $hn;

    my $key = "$app.$hn";

    my $uri;

    if ( exists $servers{$key} ) {
        $uri = $servers{$key};
    }
    else {
        die "current host ($hn) not found in server list";
    }

    return "http://$uri";
}

sub db_domain() {
    my $hn = `hostname`;
    chomp($hn);

    if ( !exists $domains{$hn} ) {
        die "current host ($hn) not found in db domain list";
    }

    return $domains{$hn};
}

sub selenium_rc_host {
    return $selenium_host;
}

1;
__END__

=head1 NAME

Pij::Test::Blackbox - Utility functions to enable black-box testing

=head1 SYNOPSIS

=head1 AUTHOR

Sean Gilbertson, sgilbertson@americanpublicmedia.org

=head1 SEE ALSO

perl(1).

=cut
