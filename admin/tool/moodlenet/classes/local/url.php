<?php


namespace tool_moodlenet\local;

/**
 * The url class, providing a representation of a url and operations on its component parts.
 *
 * @package tool\moodlenet\local
 */
class url {

    /** @var string $url the full URL string.*/
    protected $url;

    /** @var string|null $path the path component of this URL.*/
    protected $path;

    /** @var host|null $host the host component of this URL.*/
    protected $host;

    /**
     * The url constructor.
     *
     * @param string $url the URL string.
     * @throws \coding_exception if the URL does not pass syntax validation.
     */
    public function __construct(string $url) {
        // This object supports URLs as per the spec, so non-ascii chars must be encoded as per IDNA rules.
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \coding_exception('Malformed URL');
        }
        $this->url = $url;
        $this->path = parse_url($url, PHP_URL_PATH);
        $this->host = parse_url($url, PHP_URL_HOST);
    }

    /**
     * Get the path component of the URL.
     *
     * @return string the path component of the URL.
     */
    public function get_path(): ?string {
        return $this->path;
    }

    /**
     * Return the domain component of the URL.
     *
     * @return string the domain component of the URL.
     */
    public function get_host(): ?string {
        return $this->host;
    }

    /**
     * Return the full URL string.
     *
     * @return string the full URL string.
     */
    public function get_value() {
        return  $this->url;
    }
}
