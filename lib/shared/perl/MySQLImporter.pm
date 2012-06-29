package MySQLImporter;

# LOAD DATA INFILE 'buffer.sql'
# INTO TABLE <tblname>
# FIELDS TERMINATED BY '<EOFld>\t'
# LINES TERMINATED BY '<EOLin>\n'
# IGNORE 0 LINES
# (fldname1, fldname2, fldname3...);

use strict;
use warnings;
use base qw(Rose::ObjectX::CAF);
use Carp;
use Path::Class;

__PACKAGE__->mk_accessors(
    qw(
        file_name
        table_name
        columns
        dbh
        delete_first
        debug
        )
);

use IO::File;

my $end_field   = "<EOFld>";
my $end_line    = "<EOLin>";
my $buffer_size = 100000;

sub init {
    my $self = shift;
    $self->SUPER::init(@_);
    die "table_name required" unless $self->table_name;
    die "columns required" unless ref $self->columns;
    my $dir = Path::Class::dir("/tmp/mysql-import");
    $dir->mkpath();
    my $tbl  = $self->table_name;
    my $file = "$dir/$tbl";
    $self->{file_name} ||= $file;
    $self->{_buffer} = "";
    $self->{_fh}     = new IO::File "> $self->{file_name}"
        or die "cannot write $self->{file_name}: $!";
    $self->{_fh}->binmode(':utf8');
    $self->{delete_first} = 0 unless defined $self->{delete_first};
    return $self;
}

sub buffer {
    my $self = shift;
    my $str  = shift;
    if ( !defined($str) ) {
        $str = '\N';
    }
    else {

        # escape literal \ marks, as in json strings
        $str =~ s/\\/\\\\/g;
    }
    $self->{_buffer} .= $str . $end_field;
}

sub end_record {
    my $self = shift;
    $self->{_buffer} .= $end_line . "\n";
    if ( length( $self->{_buffer} ) > $buffer_size ) {
        $self->flush();
    }
}

sub flush {
    my $self = shift;

    print { $self->{_fh} } $self->{_buffer}
        or die "Can't print to filehandle: $!";

    #warn "wrote " . length( $self->{_buffer} ) . " bytes to file\n";
    $self->{_buffer} = "";
}

sub get_load_cmd {
    my $self  = shift;
    my $table = $self->table_name;
    my $cmd   = qq/LOAD DATA LOCAL INFILE ? /;
    $cmd .= qq/REPLACE /;           # replace duplicate keys --TODO: log this?
    $cmd .= qq/INTO TABLE $table /;

    # see http://bugs.mysql.com/bug.php?id=10195
    $cmd .= qq/ character set utf8 /;
    $cmd .= qq/FIELDS TERMINATED BY '$end_field' /;
    $cmd .= qq/LINES TERMINATED BY '$end_line\\n' /;
    $cmd .= qq/IGNORE 0 LINES (/ . join( ',', @{ $self->columns } ) . qq/)/;
    return $cmd;
}

sub load {
    my $self = shift;
    $self->flush();
    $self->{_fh}->close();    # IMPORTANT!!
    my $dbh   = $self->dbh or croak "dbh required";
    my $table = $self->table_name;
    my $file  = $self->file_name;

    if ( $self->delete_first ) {
        $dbh->do("delete from $table");
    }

    my $cmd = $self->get_load_cmd();

    # see http://bugs.mysql.com/bug.php?id=10195
    $dbh->do("SET character_set_database=utf8");

    #warn "do $cmd\n";

    $dbh->{RaiseError} = 1;

    eval {
        my $sth = $dbh->prepare("select count(*) from $table");
        $sth->execute();
        my $before = $sth->fetch->[0];
        my $ret = $dbh->do( $cmd, undef, $file );
        $sth->execute();
        my $after    = $sth->fetch->[0];
        my $inserted = $after - $before;
        $self->debug and warn "loaded $inserted rows [ret=$ret]\n";
    };
    if ($@) {
        croak "$cmd for file '$file' failed with $@";
    }

}

1;

