<?php

namespace App\Http\Controllers\Soap;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class LibrarySoapController extends Controller
{
    private const SOAP_NS = 'http://schemas.xmlsoap.org/soap/envelope/';
    private const SERVICE_NS = 'http://sim.del.ac.id/soap/library';

    public function wsdl(): Response
    {
        $serviceUrl = htmlspecialchars(route('soap.sipus.check-clearance'), ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $serviceNs = self::SERVICE_NS;

        $xml = <<<XML
            
            <wsdl:definitions xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/"
                xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/"
                xmlns:xsd="http://www.w3.org/2001/XMLSchema"
                xmlns:tns="{$serviceNs}"
                targetNamespace="{$serviceNs}">
              <wsdl:types>
                <xsd:schema targetNamespace="{$serviceNs}" elementFormDefault="qualified">
                  <xsd:element name="CheckClearanceStatus">
                    <xsd:complexType><xsd:sequence><xsd:element name="nim_peminjam" type="xsd:string"/></xsd:sequence></xsd:complexType>
                  </xsd:element>
                  <xsd:element name="CheckClearanceStatusResponse">
                    <xsd:complexType><xsd:sequence><xsd:element name="active_loans" type="xsd:nonNegativeInteger"/><xsd:element name="is_cleared" type="xsd:boolean"/></xsd:sequence></xsd:complexType>
                  </xsd:element>
                </xsd:schema>
              </wsdl:types>
              <wsdl:message name="CheckClearanceStatusInput"><wsdl:part name="parameters" element="tns:CheckClearanceStatus"/></wsdl:message>
              <wsdl:message name="CheckClearanceStatusOutput"><wsdl:part name="parameters" element="tns:CheckClearanceStatusResponse"/></wsdl:message>
              <wsdl:portType name="LibrarySoapPortType">
                <wsdl:operation name="CheckClearanceStatus">
                  <wsdl:input message="tns:CheckClearanceStatusInput"/>
                  <wsdl:output message="tns:CheckClearanceStatusOutput"/>
                </wsdl:operation>
              </wsdl:portType>
              <wsdl:binding name="LibrarySoapBinding" type="tns:LibrarySoapPortType">
                <soap:binding style="document" transport="http://schemas.xmlsoap.org/soap/http"/>
                <wsdl:operation name="CheckClearanceStatus">
                  <soap:operation soapAction="{$serviceNs}#CheckClearanceStatus"/>
                  <wsdl:input><soap:body use="literal"/></wsdl:input>
                  <wsdl:output><soap:body use="literal"/></wsdl:output>
                </wsdl:operation>
              </wsdl:binding>
              <wsdl:service name="LibrarySoapService">
                <wsdl:port name="LibrarySoapPort" binding="tns:LibrarySoapBinding">
                  <soap:address location="{$serviceUrl}"/>
                </wsdl:port>
              </wsdl:service>
            </wsdl:definitions>
            XML;

        return response($xml, 200, ['Content-Type' => 'text/xml; charset=UTF-8']);
    }

    public function checkClearance(Request $request): Response
    {
        $payload = $request->getContent();
        $hasDoctype = preg_match('/<!\s*(DOCTYPE|ENTITY)\b/i', $payload) === 1;
        $document = new \DOMDocument();
        $document->resolveExternals = false;
        $document->substituteEntities = false;
        $previousInternalErrors = libxml_use_internal_errors(true);

        try {
            $validXml = ! $hasDoctype && $document->loadXML($payload, LIBXML_NONET | LIBXML_NOBLANKS);
        } catch (Throwable) {
            $validXml = false;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousInternalErrors);
        }

        if (! $validXml || ! $document->documentElement
            || $document->documentElement->localName !== 'Envelope'
            || $document->documentElement->namespaceURI !== self::SOAP_NS) {
            return $this->fault('SOAP request berisi XML yang tidak valid.');
        }

        $xpath = new \DOMXPath($document);
        $body = $xpath->query('//*[local-name()="Body"]')->item(0);
        $nimNodes = $body instanceof \DOMElement
            ? $xpath->query('.//*[local-name()="nim_peminjam"]', $body)
            : false;
        $nim = $nimNodes !== false && $nimNodes->length === 1
            ? strtoupper(trim((string) $nimNodes->item(0)?->textContent))
            : '';

        if ($nim === '') {
            return $this->fault('Elemen nim_peminjam wajib diisi tepat satu kali.');
        }

        $activeLoans = Peminjaman::query()
            ->where('nim_peminjam', $nim)
            ->where('status', 'dipinjam')
            ->count();
        $isCleared = $activeLoans === 0 ? 'true' : 'false';
        $serviceNs = self::SERVICE_NS;
        $soapNs = self::SOAP_NS;

        $xml = <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <soap:Envelope xmlns:soap="{$soapNs}" xmlns:m="{$serviceNs}">
              <soap:Body>
                <m:CheckClearanceStatusResponse>
                  <m:active_loans>{$activeLoans}</m:active_loans>
                  <m:is_cleared>{$isCleared}</m:is_cleared>
                </m:CheckClearanceStatusResponse>
              </soap:Body>
            </soap:Envelope>
            XML;

        return response($xml, 200, ['Content-Type' => 'text/xml; charset=UTF-8']);
    }

    private function fault(string $message): Response
    {
        $escapedMessage = htmlspecialchars($message, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $soapNs = self::SOAP_NS;

        $xml = <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <soap:Envelope xmlns:soap="{$soapNs}">
              <soap:Body>
                <soap:Fault>
                  <faultcode>soap:Client</faultcode>
                  <faultstring>{$escapedMessage}</faultstring>
                </soap:Fault>
              </soap:Body>
            </soap:Envelope>
            XML;

        return response($xml, 400, ['Content-Type' => 'text/xml; charset=UTF-8']);
    }
}
